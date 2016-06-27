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
 * Authorize.net CIM API Hosted Profile Page Response Class
 *
 * Parses the hosted profile page response
 *
 * @since 2.0.0
 */
class WC_Authorize_Net_CIM_API_Hosted_Profile_Page_Response extends WC_Authorize_Net_CIM_API_Response {


	/**
	 * Return the page token for generating the hosted profile page
	 *
	 * @since 2.0.0
	 * @return null|string page token
	 */
	public function get_page_token() {

		return ! empty( $this->response->token ) ? (string) $this->response->token : null;
	}


}
