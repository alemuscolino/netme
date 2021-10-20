<?php

/**
 * 
 * Usage Example:
 * <?php
 * try {
 *     Session::w('foo', 'bar');
 * 
 *     echo Session::r('foo');
 * }
 * catch (Exception $e) {
 *     // do something
 * }
 * ?>
 *
 */

class Session
{
    /**
     * Session Age.
     * 
     * The number of seconds of inactivity before a session expires.
     * 
     * @var integer
     */
    protected static $SESSION_AGE = 3600;
		
		 /**
     * Max request time.
     * 
     * The number of seconds between max requests.
     * 
     * @var integer
     */
    protected static $max_requests_time = 300;
		
		/**
     * Max requests.
     * 
     * The number of request that an user can launch in a session age time.
     * 
     * @var integer
     */
    protected static $max_requests = 5;
		
		
		/**
			* Database
			*
			*	@object, Object for database
		*/
		private static $db;
		
		/**
			* IP address
			*
			*	@string, Ip address of session
		*/
		private static $ip;
		
		
		/**
			* GEO info
			*
			*	@object
		*/
		private static $geo;
    
    /**
     * Writes a value to the current session data.
     * 
     * @param string $key String identifier.
     * @param mixed $value Single value or array of values to be written.
     * @return mixed Value or array of values written.
     * @throws Exception Session key is not a string value.
     */
		 
		
    public static function write($key, $value)
    {
        if ( !is_string($key) )
            throw new Exception('Session key must be string value');
        self::_init();
        $_SESSION[$key] = $value;
        if(self::_age())
					return $value;
				return false;
    }
    
    /**
     * Alias for {@link Session::write()}.
     * 
     * @see Session::write()
     * @param string $key String identifier.
     * @param mixed $value Single value or array of values to be written.
     * @return mixed Value or array of values written.
     * @throws Exception Session key is not a string value.
     */
    public static function w($key, $value)
    {
        return self::write($key, $value);
    }
    
    /**
     * Reads a specific value from the current session data.
     * 
     * @param string $key String identifier.
     * @param boolean $child Optional child identifier for accessing array elements.
     * @return mixed Returns a string value upon success.  Returns false upon failure.
     * @throws Exception Session key is not a string value.
     */
    public static function read($key, $child = false)
    {
        if ( !is_string($key) )
            throw new Exception('Session key must be string value');
        self::_init();
        if (isset($_SESSION[$key]))
        {
            if(self::_age()){
            
							if (false == $child)
							{
									return $_SESSION[$key];
							}
							else
							{
									if (isset($_SESSION[$key][$child]))
									{
											return $_SESSION[$key][$child];
									}
							}
						}
        }
        return false;
    }
    
    /**
     * Alias for {@link Session::read()}.
     * 
     * @see Session::read()
     * @param string $key String identifier.
     * @param boolean $child Optional child identifier for accessing array elements.
     * @return mixed Returns a string value upon success.  Returns false upon failure.
     * @throws Exception Session key is not a string value.
     */
    public static function r($key, $child = false)
    {
        return self::read($key, $child);
    }
    
    /**
     * Deletes a value from the current session data.
     * 
     * @param string $key String identifying the array key to delete.
     * @return void
     * @throws Exception Session key is not a string value.
     */
    public static function delete($key)
    {
        if ( !is_string($key) )
            throw new Exception('Session key must be string value');
        self::_init();
        unset($_SESSION[$key]);
        self::_age();
    }
    
    /**
     * Alias for {@link Session::delete()}.
     * 
     * @see Session::delete()
     * @param string $key String identifying the key to delete from session data.
     * @return void
     * @throws Exception Session key is not a string value.
     */
    public static function d($key)
    {
        self::delete($key);
    }
    
    /**
     * Echos current session data.
     * 
     * @return void
     */
    public static function dump()
    {
        self::_init();
        echo nl2br(print_r($_SESSION));
    }

    /**
     * Starts or resumes a session by calling {@link Session::_init()}.
     * 
     * @see Session::_init()
     * @return boolean Returns true upon success and false upon failure.
     * @throws Exception Sessions are disabled.
     */
    public static function start()
    {
        // this function is extraneous
        return self::_init();
    }
    
    /**
     * Expires a session if it has been inactive for a specified amount of time.
     * 
     * @return void
     * @throws Exception() Throws exception when read or write is attempted on an expired session.
     */
    private static function _age()
    {
        $last = isset($_SESSION['LAST_ACTIVE']) ? $_SESSION['LAST_ACTIVE'] : false ;
        
        if (false !== $last && (time() - $last > self::$SESSION_AGE))
        {
            self::destroy();
            return false;
        }
				
        $_SESSION['LAST_ACTIVE'] = time();
				self::$db->query("UPDATE sessions SET last_active = CURRENT_TIMESTAMP() WHERE ip = :ip", array("ip"=>self::$ip));

				return true;
    }
    
    /**
     * Returns current session cookie parameters or an empty array.
     * 
     * @return array Associative array of session cookie parameters.
     */
    public static function params()
    {
        $r = array();
        if ( '' !== session_id() )
        {
            $r = session_get_cookie_params();
        }
        return $r;
    }
    
    /**
     * Closes the current session and releases session file lock.
     * 
     * @return boolean Returns true upon success and false upon failure.
     */
    public static function close()
    {
        if ( '' !== session_id() )
        {
            return session_write_close();
        }
        return true;
    }
    
    /**
     * Alias for {@link Session::close()}.
     * 
     * @see Session::close()
     * @return boolean Returns true upon success and false upon failure.
     */
    public static function commit()
    {
        return self::close();
    }
    
    /**
     * Removes session data and destroys the current session.
     * 
     * @return void
     */
    public static function destroy()
    {
        if ( '' !== session_id() )
        {
            $_SESSION = array();

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
						//Deleting session from db
						$ip = self::get_ip();
						if(!empty($ip))
							self::$db->query("DELETE FROM sessions WHERE ip = :ip", array("ip"=>$ip));
            session_destroy();
        }
    }
    
    /**
     * Initializes a new secure session or resumes an existing session.
     * 
     * @return boolean Returns true upon success and false upon failure.
     * @throws Exception Sessions are disabled.
     */
    private static function _init()
    {
				//Init database object
				self::$db = new Database();
				self::$ip = self::get_ip();
				self::$geo = self::get_geo();
				
				$db_session = self::$db->query("SELECT * FROM sessions WHERE ip = :ip AND TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP(), last_active)) < :session_age", array("ip"=>self::$ip, "session_age"=>self::$SESSION_AGE));
				
				if (function_exists('session_status'))
        {
            // PHP 5.4.0+
            if (session_status() == PHP_SESSION_DISABLED)
                throw new Exception();
        }

        if ( '' === session_id() )
        {
            $secure = true;
            $httponly = true;

            // Disallow session passing as a GET parameter.
            // Requires PHP 4.3.0
            if (ini_set('session.use_only_cookies', 1) === false) {
                throw new Exception();
            }

            // Mark the cookie as accessible only through the HTTP protocol.
            // Requires PHP 5.2.0
            if (ini_set('session.cookie_httponly', 1) === false) {
                throw new Exception();
            }

            // Ensure that session cookies are only sent using SSL.
            // Requires a properly installed SSL certificate.
            // Requires PHP 4.0.4 and HTTPS
            //if (ini_set('session.cookie_secure', 1) === false) {
            //    throw new Exception();
            //}

            $params = session_get_cookie_params();
            session_set_cookie_params($params['lifetime'],
                $params['path'], $params['domain'],
                $secure, $httponly
            );
						
						if(session_start()){
							//Generate a token for requests and set on db 
							//if(empty(self::r('token')) && '' !== session_id()){
							if(self::_age()){
								if(!isset($_SESSION['token'])){
									//No token set, Generate a token for requests and set on db 
									//Test if from same IP ther are valid token
									if(count($db_session)> 0 && !empty($db_session[0]['token'])){
										//There is a valid token not regenerate it
										$_SESSION['token'] = $db_session[0]['token'];
										self::$db->query("UPDATE sessions SET last_active = CURRENT_TIMESTAMP() WHERE ip = :ip", array("ip"=>self::$ip));
										return true;
									}
								}else{
									if(count($db_session) == 0){
											self::$db->query("INSERT INTO sessions (ip, location, country, host, token) VALUES (:ip, :location, :country, :host, :token)", array("ip"=>self::$ip, "location"=>self::$geo["location"], "country"=>self::$geo["country"], "host"=>self::$geo["host"], "token"=>$_SESSION['token']));
									}
									return true;
								}
							}else{
								//Restart session _age function did destroy latest session_start()
								session_start();
							}
							//No valid token or expired session, create new token and store it in db
							$token = md5(session_id());
							$_SESSION['token'] = $token;
							self::$db->query("INSERT INTO sessions (ip, location, country, host, token) VALUES (:ip, :location, :country, :host, :token)", array("ip"=>self::$ip, "location"=>self::$geo["location"], "country"=>self::$geo["country"], "host"=>self::$geo["host"], "token"=>$token));
							return true;
						}else{
							return false;
						}
        }
        // Helps prevent hijacking by resetting the session ID at every request.
        // Might cause unnecessary file I/O overhead?
        // TODO: create config variable to control regenerate ID behavior
        return session_regenerate_id(true);
    }
		
		
		/* public static function check(){
			if(!empty(self::r('token'))){
				$token = self::r('token');
				$result = self::$db->query("SELECT * FROM sessions WHERE ip = :ip AND token = :token", array("ip"=>self::$ip, "token"=>$token));
				if(count($result)> 0){
					if($result[0]["n_requests"] < self::$max_requests){
						return true;
					}else{
						if((strtotime(date("Y-m-d H:i:s")) - strtotime($result[0]["last_request"])) > self::$SESSION_AGE){
							//We can reset n_request value because session_age second elapsed
							self::$db->query("UPDATE sessions SET last_request = CURRENT_TIMESTAMP(), last_active = CURRENT_TIMESTAMP(), n_requests = 1 WHERE ip = :ip and token = :token", array("ip"=>self::$ip, "token"=>$token));
							return true;
						} 
					}
				}
			}
			return false;
		} */
		
		
		/**
     * Send a request
     * 
     * @return int Returns id session on success -1 on failure.
     */
		public static function request(){
			if(!empty(self::r('token'))){
				$token = self::r('token');
				$result = self::$db->query("SELECT * FROM sessions WHERE ip = :ip AND token = :token", array("ip"=>self::$ip, "token"=>$token));
				if(count($result)> 0){
					if($result[0]["n_requests"] < self::$max_requests){
						self::$db->query("UPDATE sessions SET last_request = CURRENT_TIMESTAMP(), last_active = CURRENT_TIMESTAMP(), n_requests = n_requests + 1 WHERE ip = :ip and token = :token", array("ip"=>self::$ip, "token"=>$token));
						return $token;
					}
					if((strtotime(date("Y-m-d H:i:s")) - strtotime($result[0]["last_request"])) > self::$max_requests_time){
						//We can reset n_request value because session_age second elapsed
						self::$db->query("UPDATE sessions SET last_request = CURRENT_TIMESTAMP(), last_active = CURRENT_TIMESTAMP(), n_requests = 1 WHERE ip = :ip and token = :token", array("ip"=>self::$ip, "token"=>$token));
						return $token;
					}
				}									
			}
			return null;
		}
		
		
		/**
     * Get time to expire
     * 
     * @return int 
     */
		public static function expire(){
			$token = self::r('token');
			if(!empty($token)){
				$result = self::$db->query("SELECT * FROM sessions WHERE ip = :ip AND token = :token", array("ip"=>self::$ip, "token"=>$token));
				if(count($result)> 0 && !empty($result[0]['last_request'])){
					return intval((self::$SESSION_AGE - (time() - strtotime($result[0]['last_request'])))/60);
				}
			}
			return 0;
		}
		
		private function get_ip() {
			$ipaddress = '';
			if (getenv('HTTP_CLIENT_IP'))
					$ipaddress = getenv('HTTP_CLIENT_IP');
			else if(getenv('HTTP_X_FORWARDED_FOR'))
					$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
			else if(getenv('HTTP_X_FORWARDED'))
					$ipaddress = getenv('HTTP_X_FORWARDED');
			else if(getenv('HTTP_FORWARDED_FOR'))
					$ipaddress = getenv('HTTP_FORWARDED_FOR');
			else if(getenv('HTTP_FORWARDED'))
				 $ipaddress = getenv('HTTP_FORWARDED');
			else if(getenv('REMOTE_ADDR'))
					$ipaddress = getenv('REMOTE_ADDR');
			else
					$ipaddress = 'UNKNOWN';
			return $ipaddress;
		}
		
		private function get_geo(){
			$geo = [];
			$json = file_get_contents('http://www.geoplugin.net/json.gp?ip='.self::$ip);
			$json = str_replace("geoPlugin(","",$json);
			$json = str_replace(")","", $json);
			$json = json_decode($json);
			$geo["location"] = $json->geoplugin_city.' - '.$json->geoplugin_region;
			$geo["country"] = $json->geoplugin_countryCode;
			$geo["host"] = gethostname();
			return $geo;
		}
}

//END