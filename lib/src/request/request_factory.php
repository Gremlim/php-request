<?php
namespace request;

class request_exception_no_cli extends request_exception {
	public function __construct() {parent::__construct("cannot create web request in cli mode.");}
};

class request_factory {

	public static function	from_apache_request() {

		if(php_sapi_name()=="cli") {
			throw new request_exception_no_cli;
		}

		$headers=getallheaders();
		$method=$_SERVER['REQUEST_METHOD'];
		$uri=$_SERVER['REQUEST_URI'];
		$protocol=$_SERVER['SERVER_PROTOCOL'];
		$body=self::can_get_body_from_input($headers, $method) ?
					file_get_contents('php://input') :
					raw_request_body_tools::raw_body_from_php_parsed_data($_POST, $_FILES, $headers);

		return self::is_multipart($headers) ?
				new multipart_request($method, $uri, $protocol, $headers, $body) :
				new urlencoded_request($method, $uri, $protocol, $headers, $body);
	}

	private static function	is_multipart($_headers) {

			//TODO: Mind the casing
			return isset($_headers['Content-Type']) && false!==strpos($_headers['Content-Type'], 'multipart/form-data');
	}

	//!Body cannot be retrieved from php://input when there is a multipart/form-data content type in a post request.
	private static function can_get_body_from_input(array $_headers, $method) {

		//TODO: What about headers upper and lowercasing...
		return ! ('POST'===strtoupper($method) && isset($_headers['Content-Type']) && false!==strpos($_headers['Content-Type'], 'multipart/form-data'));
	}
}