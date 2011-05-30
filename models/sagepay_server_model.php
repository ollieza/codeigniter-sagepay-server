<?php

class Sagepay_server_model extends CI_Model { 

	// Protected or private properties
	protected $_table;
	
	// Constructor
	public function __construct()
	{
		// Define the sagepay_table name
		$this->_table['sagepay_payments'] = 'sagepay_payments';
	}
	
	// --------------------------------------------------------------------
	
	function add_transaction($data = NULL)
	{
		$transaction['LastUpdated'] = date("Y-m-d H:i:s");
		
		$this->db->insert($this->_table['sagepay_payments'], $data);
					  
		if ($this->db->affected_rows() == '1')
		{
			return $this->db->insert_id();           
		} 
	
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	function update_transaction($data = NULL, $VendorTxCode = NULL)
	{
		if ($VendorTxCode == NULL || $data == NULL)
		{
			return FALSE;
		}
		
		$this->db->where('VendorTxCode', $VendorTxCode);
		$this->db->update($this->_table['sagepay_payments'], $data);
 
		if ($this->db->affected_rows() == '1')
		{
			return TRUE;
		}
 
		return FALSE;
	}

	// --------------------------------------------------------------------
	
	function get_transaction($VendorTxCode = NULL, $VPSTxId = NULL)
	{
		$this->db->select('*');
		$this->db->where('VendorTxCode', $VendorTxCode);

		if ($VPSTxId) // when we initially need to validate the post we use this which is sent via a post from sagepay
		{
			$this->db->where('VPSTxId', $VPSTxId);
		}

		$query = $this->db->get($this->_table['sagepay_payments']); 

		if ($query->num_rows() == 1)
		{
			return $query->row();
		}

     	return FALSE;
	}

	// --------------------------------------------------------------------
	
	function get_security_key($VendorTxCode = NULL, $VPSTxId = NULL)
	{
		$this->db->select('SecurityKey');
		$this->db->where('VendorTxCode', $VendorTxCode);
		$this->db->where('VPSTxId', $VPSTxId);
		$query = $this->db->get($this->_table['sagepay_payments']); 
		
		if ($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->SecurityKey;
		}
		
     	return FALSE;
	}
	
	// --------------------------------------------------------------------
}
?>