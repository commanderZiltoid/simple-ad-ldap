<?php
/*
 * This is an extremely basic Ldap authentication class which uses the proxy user account
 * method to bind since this is what we are using. Additional functionality can
 * be added as necessary.
 */


// if your application utilizes namespaces define the namespace of this class below
//namespace App\Libraries;

class Ldap{
    
    /*
     * Private configuration variables. Configure these for your setup.
     */
    private $_host = '';
    private $_port = 1234;
    private $_basedn = '';
    private $_login_attribute  = '';
    private $_proxy_user = '';
    private $_proxy_pass = '';
    private $_ldapconn;
    
    public $error_message = false;
    
    
    /*
     * 
     */
    private function _clean_user_ad_groups($user_groups)
    {
        
        $groups_clean = array();
        foreach($user_groups as $key => $val){
            
            $row = explode(',', $val);
            $group = str_replace('CN=', '', $row[0]);

            array_push($groups_clean, $group);

        }
        
        return $groups_clean;
        
    }
    
    
    /*
     * Authenticate the given username/password
     */
    public function authenticate($username, $password)
    {
        
        $this->_ldapconn = ldap_connect($this->_host, $this->_port);
        
        if(!$this->_ldapconn){
            $this->error_message = 'Unable to connect to active directory host';
            return false;
        }
        
        // Connect using proxy_user/proxy_pass since this is the process the state uses
        $bind = ldap_bind($this->_ldapconn, $this->_proxy_user, $this->_proxy_pass);
        if(!$bind){
            $this->error_message = 'Unable to perform proxy bind';
            return false;
        }
        
        $search = ldap_search($this->_ldapconn, $this->_basedn, '(' . $this->_login_attribute . '=' . $username . ')');
        $entries = ldap_get_entries($this->_ldapconn, $search);
        
        if($entries['count'] == 1){
            
            $binddn = $entries[0]['dn'];
            
            try{
                
                $bind = @ldap_bind($this->_ldapconn, $binddn, $password);
                
            } catch (Exception $ex) {
                
                $bind = false;
                
            }
            
            if(!$bind){
                
                $this->error_message = 'Failed login attempt for user: ' . $username;
                return false;
                
            } else {
                
                $cn = $entries[0]['cn'][0];
                $groups = $entries[0]['memberof'];
                
                return array(
                  
                    'groups'    =>  $this->_clean_user_ad_groups($groups),
                    'name'      =>  $cn,
                    'email'     =>  $entries[0]['mail'][0]
                    
                );
                
            }
            
        } else {
            
            $this->error_message = 'No Entries';
            return false;
            
        }
        
        
    }
    
}