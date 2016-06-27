<?php
/**
 * WooCommerce Authorize.net CIM Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Authorize.net CIM Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Authorize.net CIM Gateway for your
 * needs please refer to http://docs.woothemes.com/document/authorize-net-cim/
 *
 * @package   WC-Gateway-Authorize-Net-CIM/API/Response
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2015, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Authorize.net CIM Response Class
 *
 * Provides helper methods for parsing responses
 *
 * Note: the (string) casts here are critical to convert the objects into valid
 * strings, without these you'll tend to get untraceable errors like
 * "Serialization of 'SimpleXMLElement' is not allowed"
 *
 * @since 2.0.0
 * @see SV_WC_Payment_Gateway_API_Response
 */
abstract class WC_Authorize_Net_CIM_API_Response implements SV_WC_API_Response {


	/** @var WC_Authorize_Net_CIM_API_Request request that resulted in this response */
	protected $request;

	/** @var string string representation of this response */
	private $raw_response_xml;

	/** @var SimpleXMLElement response XML object */
	protected $response;

	/** @var string the response root element name */
	protected $name;


	/**
	 * Build a response object from the raw response xml
	 *
	 * @since 2.0.0
	 * @param SV_WC_Payment_Gateway_API_Request $request that resulted in this response
	 * @param string $raw_response_xml the raw response XML
	 */
	public function __construct( $request, $raw_response_xml ) {

		$this->request = $request;

		$this->raw_response_xml = $raw_response_xml;

		// Remove namespace as SimpleXML throws warnings with invalid namespace URI provided by Authorize.net
		$raw_response_xml = preg_replace( '/[[:space:]]xmlns[^=]*="[^"]*"/i', '', $raw_response_xml );

		// LIBXML_NOCDATA ensures that any XML fields wrapped in [CDATA] will be included as text nodes
		$this->response = new SimpleXMLElement( $raw_response_xml, LIBXML_NOCDATA );

		// root element name, useful for identifying exact type of response, e.g. `createTransactionResponse`
		// note that for hard errors, the response type will be `ErrorResponse`
		$this->name = $this->response->getName();
	}


	/**
	 * Checks if response contains an API error code
	 *
	 * @since 2.0.0
	 * @return bool true if has API error, false otherwise
	 */
	public function has_api_error() {

		if ( ! isset( $this->response->messages->resultCode ) ) {
			return true;
		}

		return 'error' == strtolower( (string) $this->response->messages->resultCode );
	}


	/**
	 * Gets the API error code
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_api_error_code() {

		if ( ! isset( $this->response->messages->message->code ) ) {
			return __( 'N/A', WC_Authorize_Net_CIM::TEXT_DOMAIN );
		}

		return (string) $this->response->messages->message->code;
	}


	/**
	 * Gets the API error message
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_api_error_message() {

		if ( ! isset( $this->response->messages->message->text ) ) {
			return __( 'N/A', WC_Authorize_Net_CIM::TEXT_DOMAIN );
		}

		$message = (string) $this->response->messages->message->text;

		return $message;
	}


	/**
	 * Gets any user-defined fields associated with the transaction response
	 *
	 * @since 2.0.0
	 * @return array transaction user-defined fields
	 */
	public function get_user_defined_fields() {

		$fields = array();

		if ( isset( $this->response_xml->transactionResponse->userFields->userField ) ) {

			foreach ( $this->response_xml->transactionResponse->userFields->userField as $user_field ) {

				$fields[ (string) $user_field->name ] = (string) $user_field->value;
			}
		}

		return $fields;
	}


	/**
	 * Returns a message appropriate for a frontend user.  This should be used
	 * to provide enough information to a user to allow them to resolve an
	 * issue on their own, but not enough to help nefarious folks fishing for
	 * info.
	 *
	 * @since 2.0.0
	 * @return string user message, if there is one
	 */
	public function get_user_message() {

		$helper = new WC_Authorize_Net_CIM_API_Response_Message_Handler( $this );

		return $helper->get_message();
	}


	/**
	 * Returns the string representation of this response
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::to_string()
	 * @return string response
	 */
	public function to_string() {

		$string = $this->raw_response_xml;

		$dom = new DOMDocument();

		// suppress errors for invalid XML syntax issues
		if ( @$dom->loadXML( $string ) ) {
			$dom->formatOutput = true;
			$string = $dom->saveXML();
		}

		return $string;
	}


	/**
	 * Returns the string representation of this response with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::to_string_safe()
	 * @return string response safe for logging/displaying
	 */
	public function to_string_safe() {

		// no sensitive data to mask
		return $this->to_string();
	}


	/**
	 * Returns the request object that resulted in this response
	 *
	 * @since 2.0.0
	 * @return WC_Authorize_Net_CIM_API_Request request object
	 */
	public function get_request() {

		return $this->request;
	}


}
