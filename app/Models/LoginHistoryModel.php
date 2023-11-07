<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginHistoryModel extends BaseModel
{

    public  function __construct()
    { 
        parent::__construct();
    
    }



    protected $DBGroup          = 'default';
    protected $table            = 'loginhistory';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [

'jsId',
'jsName',
'email',
'mobileNumber',
'mobileOtpStatus',
'otpTimeStamp',
'otpTryCount',
'token',
'tokenTime',
'mobileOtp'



    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


     // Changing the status of OTP to -1 when it is more than two minutes

	public function update_otp_status($table,$startTime,$endTime,$cond,$dataToUpdate)
	{
	  $builder = $this->db->table($table);
      $builder->where("NOT (otpTimeStamp BETWEEN '$startTime' AND '$endTime')");
      $builder->where($cond);
      $builder->set($dataToUpdate);
      $builder->update();
      return $builder;
	}



     // Checking the status when the otp is valid which is between two minutes

    public function check_otp_time($table,$startTime,$endTime,$cond)
	{
	  $builder = $this->db->table($table);
      $builder->where("otpTimeStamp  BETWEEN '$startTime' AND '$endTime'");
      $builder->where($cond);
      $query = $builder->get();
      $result=$query->getResultArray();
      return $result;
	
	}

 
}
