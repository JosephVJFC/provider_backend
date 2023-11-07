<?php

namespace App\Controllers\Registration;

use App\Controllers\BaseController;
use App\Models\SignupModel;
use CodeIgniter\API\ResponseTrait;
use \Firebase\JWT\JWT;

class Signup extends BaseController
{

    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->returnResponse = ['status' => '0', 'response' => ''];

    }

    public function init()
    {
        try {
            $environment_mode = '0';
            if (ENVIRONMENT == 'production') {
                $environment_mode = '1';
            }

            $mode_str = '';
            if ($this->data['app_mode'] == 'development') {
                $mode_str = $this->language('not_able_to_service_try_again');
            }
            $infoArr = array(
                'environment' => $environment_mode,
                'mode' => $this->data['app_mode'],
                'mode_str' => $mode_str,
                'site_url' => base_url(),
                'app_identity_name' => (string) APP_NAME,

            );
            $this->returnResponse['status'] = '1';
            $this->returnResponse['response'] = $infoArr;
        } catch (MongoException $ex) {
            $this->returnResponse['response'] = $this->get_api_error(401);
        }
        return $this->setResponseFormat('json')->respond($this->returnResponse, 200);
    }

    public function signup($phone_number = "", $email = "", $name = "")
    {

        $twilioService = new \App\Libraries\TwilioService();
        $phone_number = $this->request->getPostGet('mobileNumber');
        $email = $this->request->getPostGet('email');
        $name = $this->request->getPostGet('name');
        $config = config('Jwt');
        $key = $config->authKey;

        try {

            if ($phone_number != "" && $email != "" && $name != "") {
                $verifyOTP = true;
                $condition = ['email' => $email];
                $Duplicatecheck = $this->SignupModel->get_all_details("jsusers", $condition);

                if (sizeof($Duplicatecheck) >= 1) {
                    $this->returnResponse['response'] = lang('app.email_already_exist');
                } else {

                    $mobCondition = ['mobile' => $phone_number];
                    $Duplicatephonecheck = $this->SignupModel->get_all_details("jsusers", $mobCondition);

                    if (sizeof($Duplicatephonecheck) >= 1) {
                        $this->returnResponse['response'] = lang('app.phone_number_exist');

                    } else {
                        // if($verifyOTP && ENVIRONMENT=='production'){
                        if ($verifyOTP) {
                            $js_id = $this->UUID4();
                            $to = $phone_number; // Replace with the recipient's phone number

                            $payload = [
                                "jsName" => $name,
                                "email" => $email,
                                "mobileNumber" => $phone_number,

                            ];

                            $token = JWT::encode($payload, $key, $config->method);
                            // print_r($token);
                            // Based on mobile number and otp need to change the status of other otp to -1
                            $statuschange = $this->changeOtpstatus($to);
                            $otp_verifivation_number = $this->get_rand_number();
                            $message = "Hi " . " " . $name . "  " . "Your verification code for MPM: " . $otp_verifivation_number . " " . "OTP is valid for next 02 minutes. Please do not share with anyone. ";
                            $lg_history_data = array(
                                "jsName" => $name,
                                "email" => $email,
                                "mobileNumber" => $phone_number,
                                "otpTimeStamp" => date('Y-m-d H:i:s'),
                                "mobileOtp" => $otp_verifivation_number,
                                "jsId" => $js_id,
                                "token" => $token,
                                "tokenTime" => date('Y-m-d H:i:s'),

                            );

                            $otp_to = "+91" . $phone_number;
                            //  $otp_send=$twilioService->sendSMS($otp_to, $message);
                            $this->LoginHistoryModel->insert_data($lg_history_data);
                            $this->returnResponse['status'] = '1';
                            $this->returnResponse['response'] = [
                                "message" => lang('app.success'),
                                "user" => $lg_history_data,
                            ];

                        }

                    }

                }

            } else {

                $this->returnResponse['response'] = lang('app.fill_all_the_fields');
            }

            return $this->setResponseFormat('json')->respond($this->returnResponse, 200);
        } catch (\Exception $e) {

            $this->returnResponse['response'] = $this->get_api_error(401);

        }

    }

    public function otp_verification()
    {
        $otp = $this->request->getPostGet('otp');
        $mobileNumber = $this->request->getPostGet('mobileNumber');
        try {

            $Otpcheckvalid = $this->checkOtp($otp, $mobileNumber);

            if ($Otpcheckvalid['status'] == 1) {

                if ($otp != "" && $mobileNumber != "") {

                    $condition = ['mobile' => $mobileNumber];
                    $isuserCheck = $this->SignupModel->get_all_details("jsusers", $condition);

                    // need to check wether user is in usertable
                    if (sizeof($isuserCheck) == 1) {
                        $userarr = $isuserCheck[0];
                        $user_data = [
                            "userRole" => $userarr['userRole'],
                            "name" => $userarr['name'],
                            "email" => $userarr['email'],
                            "mobile" => $userarr['mobile'],
                            "jsId" => $userarr['jsId'],
                            "userStatus" => $userarr['userStatus'],
                            "token" => $userarr['token'],

                        ];

                    } else {

                        $otpcondition = ['mobileOtp' => $otp];
                        $userDetails = $this->LoginHistoryModel->get_all_details("loginhistory", $otpcondition);
                        $userarr = $userDetails[0];

                        if (sizeof($userDetails) == 1) {

                            $user_data = [
                                "userRole" => "jobseeker",
                                "name" => $userarr['jsName'],
                                "email" => $userarr['email'],
                                "mobile" => $userarr['mobileNumber'],
                                "jsId" => $userarr['jsId'],
                                "userStatus" => 1,
                                "token" => $userarr['token'],

                            ];
                        } else {

                            $this->returnResponse['response'] = lang('app.expired_otp');

                        }

                        //inserting data in js user table
                        $this->SignupModel->insert($user_data);

                        //updating mobile verifiedstatus in jsuser table
                        $update_data = ['isMobileVerified' => 1];
                        $up_cond = ['mobile' => $mobileNumber];
                        $this->SignupModel->update_data('jsusers', $update_data, $up_cond);
                    }

                    //updating mobile otpstatus in loginhistory table

                    $update_data = ['mobileOtpStatus' => 1];
                    $up_cond = ['mobileOtp' => $otp];
                    $this->LoginHistoryModel->update_data('loginhistory', $update_data, $up_cond);

                    $this->returnResponse['status'] = '1';
                    $this->returnResponse['response'] = [
                        "message" => lang('app.success'),
                        "user" => $user_data,
                    ];

                } else {
                    $this->returnResponse['response'] = lang('app.invalid_otp');
                }

            }

            return $this->setResponseFormat('json')->respond($this->returnResponse, 200);

        } catch (\Exception $e) {

            $this->returnResponse['response'] = $this->get_api_error(401);

        }

    }

    public function login($mobileNumber = "")
    {

        $twilioService = new \App\Libraries\TwilioService();
        $mobileNumber = $this->request->getPostGet('mobileNumber');
        try {

            if ($mobileNumber != "") {
                $condition = ['mobile' => $mobileNumber];
                $isuser = $this->SignupModel->get_all_details("jsusers", $condition);

                if (sizeof($isuser) == 1) {

                    $userarr = $isuser[0];
                    $to = $userarr['mobile']; // Replace with the recipient's phone number

                    $statuschange = $this->changeOtpstatus($to);
                    $otp_verifivation_number = $this->get_rand_number();
                    $message = "Hi " . " " . $userarr['name'] . "  " . "Your verification code for MPM: " . $otp_verifivation_number . " " . "OTP is valid for next 02 minutes. Please do not share with anyone. ";
                    $user_data = [

                        "jsName" => $userarr['name'],
                        "email" => $userarr['email'],
                        "mobileNumber" => $userarr['mobile'],
                        "jsId" => $userarr['jsId'],
                        "otpTimeStamp" => date('Y-m-d H:i:s'),
                        "mobileOtp" => $otp_verifivation_number,
                        "token" => $userarr['token'],
                    ];

                    $otp_to = "+91" . $userarr['mobile'];
                    //  $otp_send=$twilioService->sendSMS($otp_to, $message);

                    $this->LoginHistoryModel->insert_data($user_data);

                    $this->returnResponse['status'] = '1';
                    $this->returnResponse['response'] = [
                        "message" => lang('app.success'),
                        "user" => $user_data,
                    ];

                } else {
                    $this->returnResponse['response'] = lang('app.invalid_user');
                }

            } else {

                $this->returnResponse['response'] = lang('app.invalid_number');
            }

            return $this->setResponseFormat('json')->respond($this->returnResponse, 200);

        } catch (\Exception $e) {

            $this->returnResponse['response'] = $this->get_api_error(401);

        }
    }

    public function checkOtp($otp = "", $mobileNumber = "")
    {
        if ($otp != '' && $mobileNumber != '') {

            $cond = ['mobileOtp' => $otp, 'mobileOtpStatus' => 0];
            $end_time = date('Y-m-d H:i:s');
            $start_time = date('Y-m-d H:i:s', strtotime('-2 minutes'));
            $validOtp = $this->LoginHistoryModel->check_otp_time('loginhistory', $start_time, $end_time, $cond);

            if (sizeof($validOtp) == 1) {

                $this->returnResponse['status'] = '1';
                $this->returnResponse['response'] = lang('app.success');

            } else {
                $this->returnResponse['response'] = lang('app.otp_invalid');
            }
        } else {
            $this->returnResponse['response'] = lang('app.invalid_numbers');
        }
        return $this->returnResponse;
    }

    public function changeOtpstatus($mobileNumber = "")
    {

        if ($mobileNumber != "") {
            $condition = ['mobileOtpStatus' => 0];
            $end_time = date('Y-m-d H:i:s');
            $start_time = date('Y-m-d H:i:s', strtotime('-2 minutes'));
            $data = ['mobileOtpStatus' => -1];
            $getData = $this->LoginHistoryModel->update_otp_status('loginhistory', $start_time, $end_time, $condition, $data);
            $this->returnResponse['response'] = $getData;

        }
        return $this->setResponseFormat('json')->respond($this->returnResponse, 200);

    }

    public function resendotp()
    {
        $mobileNumber = $this->request->getPostGet('mobileNumber');
        $email = $this->request->getPostGet('email');
        $name = $this->request->getPostGet('name');

        if ($mobileNumber != "" && $email != "" && $name != "") {

            return $this->signup($mobileNumber, $email, $name);

        } elseif ($mobileNumber != "") {
            return $this->login($mobileNumber);

        } else {
            $this->returnResponse['status'] = '1';
            $this->returnResponse['response'] = lang('app.resend');
        }

        return $this->setResponseFormat('json')->respond($this->returnResponse, 200);

    }

    public function getuserbytoken()
    {
        $token = $this->request->getPostGet('token');
        try {
            $condition = ['token' => $token];
            $isuser = $this->SignupModel->get_all_details("jsusers", $condition);

        
            if (sizeof($isuser) == 1) {
              
                $userarr = $isuser[0];
                $to = $userarr['mobile']; // Replace with the recipient's phone number
                $user_data = [
                    "jsName" => $userarr['name'],
                    "email" => $userarr['email'],
                    "mobileNumber" => $userarr['mobile'],
                    "jsId" => $userarr['jsId'],
                    "token" => $userarr['token'],
                ];
                $this->returnResponse['status'] = '1';
                $this->returnResponse['response'] = [
                    "message" => lang('app.success'),
                    "user" => $user_data,
                ];

            } else {
                $this->returnResponse['response'] = lang('app.invalid_user');
            }

        } catch (\Exception $e) {

            $this->returnResponse['response'] = $this->get_api_error(401);

        }
        return $this->setResponseFormat('json')->respond($this->returnResponse, 200);
    }

	public function logout() {
        try {
			$jsId = (string)$this->request->getPostGet('jsId');
            if ($jsId != '') {
                $condition = array('jsId' => $jsId);
                $checkUser= $this->SignupModel->get_selected_fields("jsusers", $condition, array('id'));
                if (sizeof($checkUser) == 1) {
                    $userarr = $checkUser[0];
                        $update_data = array('lastLogoutdate'=>date('Y-m-d H:i:s'));
                        $this->SignupModel->update_data("jsusers", $update_data, array('id'=>$userarr['id']));
                
                    $this->returnResponse['status'] = '1';
                    $this->returnResponse['response'] = lang('app.logged_out_successfully');
                } else {
                    $this->returnResponse['response'] = lang('app.invalid_user');
                }
            } else {
                $this->returnResponse['response'] = $this->get_api_error(400);
            }
        } catch (MongoException $ex) {
            $this->returnResponse['response'] = $this->get_api_error(401);
        }
        return $this->setResponseFormat('json')->respond($this->returnResponse, 200);

    }



}
