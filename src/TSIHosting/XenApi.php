<?

/**
 * The main class for accessing the XenServer XMLRPC API
 * 
 * @author Tim Igoe <tim@tsihosting.co.uk>
 */
 
 namespace TSIHosting;
 
 if (!function_exists('curl_init')) {
  throw new Exception('XenAPI needs the CURL PHP extension.');
 
 class XenApi 
 {
   /**
    * @var string $host
    */
   private $host;
   
   /**
    * @var string $user
    */
   private $user;
   
   /**
    * @var string $pass
    */
   private $pass;
   
   /**
    * @var string $session
    */
   private $session;
  
   /**
    * Constructor
    * 
    * @param string $host
    * @param string $user
    * @param string $pass
    */
   public function __construct($host, $user, $pass)
   {
     $this->host = $host;
     $this->user = $user;
     $this->pass = $pass;
     
     // Now attempt to connect
     $this->connect(); 
   }
   
   /**
    * Main Guts of the library
    * 
    * @param string $method
    * @param array $params
    * @param bool $incsession
    * @return mixed
    */
   public function __call($method, $params)
   {
     if (!is_array($params))
       $params = array();
       
     if ($this->session)
       $params[] = $this->session;
       
     list($mod, $method) = explode('_', $method, 2);
     $name = $mod . '.' . $method;
       
     $Req = xmlrpc_encode_request($name, $params);
     
     $Res = $this->dispatch($Req);
       
     $Ret = $this->parse($Res);
     
     return $Ret;
   }
   
   /**
    * Connect
    * 
    * @return bool
    */
   public function connect()
   {
     return $this->session_login_with_password(array($this->user, $this->pass, '1.3'), false);
   }
   
   /** 
    * Parse the response from the server into something useful
    * 
    * @param array $response
    * @return bool | mixed
    */
   public function parse($response)
   {
     // Unknown return
     if (!is_array($response))
       return false;
     
     // Success  
     if ($response['Status'] == 'Success')
       return $response['value'];
       
     // Invalid Session, reconnect it
     if ($response['ErrorDescription'][0] == 'SESSION_INVALID')
       $this->connect

   }
   
    
   /**
    * Private Dispatch Function
    *  
    * @param string $Request
    * @return array
    */
   private function dispatch($Request)
   {
     $Headers = array('Content-type: text/xml', 'Content-length: ' . strlen($Request));
   
     $Curl = curl_init($this->host);
     
     curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 5);
     curl_setopt($Curl, CURLOPT_TIMEOUT, 30);
     
     curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, false);
     
     curl_setopt($Curl, CURLOPT_RETURN_TRANSFER, 1);
     
     curl_setopt($Curl, CURLOPT_HTTPHEADER, $Headers);
     curl_setopt($Curl, CURLOPT_POSTFIELDS, $Request);
     
     $Response = curl_exec($Curl);
     
     curl_close($Curl);
     
     return xmlrpc_decode($Response);
   }
 }