<style>
.rp_box_bg {
background: rgb(255,255,255); /* Old browsers */
background: -moz-linear-gradient(-45deg,  rgba(255,255,255,1) 0%, rgba(241,241,241,1) 50%, rgba(225,225,225,1) 51%, rgba(246,246,246,1) 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, right bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(50%,rgba(241,241,241,1)), color-stop(51%,rgba(225,225,225,1)), color-stop(100%,rgba(246,246,246,1))); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(-45deg,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(-45deg,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(-45deg,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* IE10+ */
background: linear-gradient(135deg,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 51%,rgba(246,246,246,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
}
.rp_box_head {
background: rgb(30,87,153); /* Old browsers */
background: -moz-linear-gradient(-45deg,  rgba(30,87,153,1) 0%, rgba(41,137,216,1) 50%, rgba(32,124,202,1) 51%, rgba(125,185,232,1) 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, right bottom, color-stop(0%,rgba(30,87,153,1)), color-stop(50%,rgba(41,137,216,1)), color-stop(51%,rgba(32,124,202,1)), color-stop(100%,rgba(125,185,232,1))); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(-45deg,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 50%,rgba(32,124,202,1) 51%,rgba(125,185,232,1) 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(-45deg,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 50%,rgba(32,124,202,1) 51%,rgba(125,185,232,1) 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(-45deg,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 50%,rgba(32,124,202,1) 51%,rgba(125,185,232,1) 100%); /* IE10+ */
background: linear-gradient(135deg,  rgba(30,87,153,1) 0%,rgba(41,137,216,1) 50%,rgba(32,124,202,1) 51%,rgba(125,185,232,1) 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
}
#del_result {
	display:none;
	background-color:#F00;
	color:#FFF;
	padding:10px;
}
#ins_result {
	display:none;
	background-color:#F00;
	color:#FFF;
	padding:10px;
}
.addloading {
	display:none;
	position: absolute;
    right: 0px;
    width: 100px;
}
.delloading {
	display:none;
	position: absolute;
    right: 0px;
    width: 100px;
}

</style>
<?php $nonce_1 = wp_create_nonce( 'addroles' ); ?>
<?php $nonce_2 = wp_create_nonce( 'delroles' ); ?>
<?php $plugin_dir = plugin_dir_url( __FILE__ ); ?>
<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2>Woocommerce Role Price Manager</h2>
		<hr />

<div class="bootstrap-wrapper">
<div class="container" style="background-color:#ddd;">
<div class="row">
        <div class="col-md-8">
        <div class="row">
        
        <form method="post" id="xart" action="options.php"> 
        <?php settings_fields( 'rp_form_fields' );
		?>
        <div id="role_result">
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">ROLE</th>
            <th style="color:#FFF;">DISCOUNT %</th>
          </tr>
        </thead>
        <tbody>
        
        <?php
		global $wp_roles;
        $roles = $wp_roles->get_names();
        foreach($roles as $role) { 
        $rolev = str_replace(' ', '_', $role);
		?>                
          <tr>
            <td><?php echo $role; ?></td>
            <td><input type="text" name="<?php echo $rolev; ?>" value="<?php echo get_option( $rolev ); ?>"/></td>           
          </tr>  
          
          <?php
		}
		?> 
                        
<tr>
<td>
</td>
<td style="font-style:italic;">* Leave empty if no role discount should be applied.</td>
</tr>                 
        </tbody>
 </table>
 </div> 
 <?php submit_button(); ?> 
 </form>
  
  </div>
  
  </div>
<div class="col-md-4">

<!-- Tax Countries -->  
<div class="row"> 
<?php
    $country_array = array(
        'AF'=>'AFGHANISTAN',
        'AL'=>'ALBANIA',
        'DZ'=>'ALGERIA',
        'AS'=>'AMERICAN SAMOA',
        'AD'=>'ANDORRA',
        'AO'=>'ANGOLA',
        'AI'=>'ANGUILLA',
        'AQ'=>'ANTARCTICA',
        'AG'=>'ANTIGUA AND BARBUDA',
        'AR'=>'ARGENTINA',
        'AM'=>'ARMENIA',
        'AW'=>'ARUBA',
        'AU'=>'AUSTRALIA',
        'AT'=>'AUSTRIA',
        'AZ'=>'AZERBAIJAN',
        'BS'=>'BAHAMAS',
        'BH'=>'BAHRAIN',
        'BD'=>'BANGLADESH',
        'BB'=>'BARBADOS',
        'BY'=>'BELARUS',
        'BE'=>'BELGIUM',
        'BZ'=>'BELIZE',
        'BJ'=>'BENIN',
        'BM'=>'BERMUDA',
        'BT'=>'BHUTAN',
        'BO'=>'BOLIVIA',
        'BA'=>'BOSNIA AND HERZEGOVINA',
        'BW'=>'BOTSWANA',
        'BV'=>'BOUVET ISLAND',
        'BR'=>'BRAZIL',
        'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
        'BN'=>'BRUNEI DARUSSALAM',
        'BG'=>'BULGARIA',
        'BF'=>'BURKINA FASO',
        'BI'=>'BURUNDI',
        'KH'=>'CAMBODIA',
        'CM'=>'CAMEROON',
        'CA'=>'CANADA',
        'CV'=>'CAPE VERDE',
        'KY'=>'CAYMAN ISLANDS',
        'CF'=>'CENTRAL AFRICAN REPUBLIC',
        'TD'=>'CHAD',
        'CL'=>'CHILE',
        'CN'=>'CHINA',
        'CX'=>'CHRISTMAS ISLAND',
        'CC'=>'COCOS (KEELING) ISLANDS',
        'CO'=>'COLOMBIA',
        'KM'=>'COMOROS',
        'CG'=>'CONGO',
        'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
        'CK'=>'COOK ISLANDS',
        'CR'=>'COSTA RICA',
        'CI'=>'COTE D IVOIRE',
        'HR'=>'CROATIA',
        'CU'=>'CUBA',
        'CY'=>'CYPRUS',
        'CZ'=>'CZECH REPUBLIC',
        'DK'=>'DENMARK',
        'DJ'=>'DJIBOUTI',
        'DM'=>'DOMINICA',
        'DO'=>'DOMINICAN REPUBLIC',
        'TP'=>'EAST TIMOR',
        'EC'=>'ECUADOR',
        'EG'=>'EGYPT',
        'SV'=>'EL SALVADOR',
        'GQ'=>'EQUATORIAL GUINEA',
        'ER'=>'ERITREA',
        'EE'=>'ESTONIA',
        'ET'=>'ETHIOPIA',
        'FK'=>'FALKLAND ISLANDS (MALVINAS)',
        'FO'=>'FAROE ISLANDS',
        'FJ'=>'FIJI',
        'FI'=>'FINLAND',
        'FR'=>'FRANCE',
        'GF'=>'FRENCH GUIANA',
        'PF'=>'FRENCH POLYNESIA',
        'TF'=>'FRENCH SOUTHERN TERRITORIES',
        'GA'=>'GABON',
        'GM'=>'GAMBIA',
        'GE'=>'GEORGIA',
        'DE'=>'GERMANY',
        'GH'=>'GHANA',
        'GI'=>'GIBRALTAR',
        'GR'=>'GREECE',
        'GL'=>'GREENLAND',
        'GD'=>'GRENADA',
        'GP'=>'GUADELOUPE',
        'GU'=>'GUAM',
        'GT'=>'GUATEMALA',
        'GN'=>'GUINEA',
        'GW'=>'GUINEA-BISSAU',
        'GY'=>'GUYANA',
        'HT'=>'HAITI',
        'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
        'VA'=>'HOLY SEE (VATICAN CITY STATE)',
        'HN'=>'HONDURAS',
        'HK'=>'HONG KONG',
        'HU'=>'HUNGARY',
        'IS'=>'ICELAND',
        'IN'=>'INDIA',
        'ID'=>'INDONESIA',
        'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
        'IQ'=>'IRAQ',
        'IE'=>'IRELAND',
        'IL'=>'ISRAEL',
        'IT'=>'ITALY',
        'JM'=>'JAMAICA',
        'JP'=>'JAPAN',
        'JO'=>'JORDAN',
        'KZ'=>'KAZAKSTAN',
        'KE'=>'KENYA',
        'KI'=>'KIRIBATI',
        'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
        'KR'=>'KOREA REPUBLIC OF',
        'KW'=>'KUWAIT',
        'KG'=>'KYRGYZSTAN',
        'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
        'LV'=>'LATVIA',
        'LB'=>'LEBANON',
        'LS'=>'LESOTHO',
        'LR'=>'LIBERIA',
        'LY'=>'LIBYAN ARAB JAMAHIRIYA',
        'LI'=>'LIECHTENSTEIN',
        'LT'=>'LITHUANIA',
        'LU'=>'LUXEMBOURG',
        'MO'=>'MACAU',
        'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
        'MG'=>'MADAGASCAR',
        'MW'=>'MALAWI',
        'MY'=>'MALAYSIA',
        'MV'=>'MALDIVES',
        'ML'=>'MALI',
        'MT'=>'MALTA',
        'MH'=>'MARSHALL ISLANDS',
        'MQ'=>'MARTINIQUE',
        'MR'=>'MAURITANIA',
        'MU'=>'MAURITIUS',
        'YT'=>'MAYOTTE',
        'MX'=>'MEXICO',
        'FM'=>'MICRONESIA, FEDERATED STATES OF',
        'MD'=>'MOLDOVA, REPUBLIC OF',
        'MC'=>'MONACO',
        'MN'=>'MONGOLIA',
        'MS'=>'MONTSERRAT',
        'MA'=>'MOROCCO',
        'MZ'=>'MOZAMBIQUE',
        'MM'=>'MYANMAR',
        'NA'=>'NAMIBIA',
        'NR'=>'NAURU',
        'NP'=>'NEPAL',
        'NL'=>'NETHERLANDS',
        'AN'=>'NETHERLANDS ANTILLES',
        'NC'=>'NEW CALEDONIA',
        'NZ'=>'NEW ZEALAND',
        'NI'=>'NICARAGUA',
        'NE'=>'NIGER',
        'NG'=>'NIGERIA',
        'NU'=>'NIUE',
        'NF'=>'NORFOLK ISLAND',
        'MP'=>'NORTHERN MARIANA ISLANDS',
        'NO'=>'NORWAY',
        'OM'=>'OMAN',
        'PK'=>'PAKISTAN',
        'PW'=>'PALAU',
        'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
        'PA'=>'PANAMA',
        'PG'=>'PAPUA NEW GUINEA',
        'PY'=>'PARAGUAY',
        'PE'=>'PERU',
        'PH'=>'PHILIPPINES',
        'PN'=>'PITCAIRN',
        'PL'=>'POLAND',
        'PT'=>'PORTUGAL',
        'PR'=>'PUERTO RICO',
        'QA'=>'QATAR',
        'RE'=>'REUNION',
        'RO'=>'ROMANIA',
        'RU'=>'RUSSIAN FEDERATION',
        'RW'=>'RWANDA',
        'SH'=>'SAINT HELENA',
        'KN'=>'SAINT KITTS AND NEVIS',
        'LC'=>'SAINT LUCIA',
        'PM'=>'SAINT PIERRE AND MIQUELON',
        'VC'=>'SAINT VINCENT AND THE GRENADINES',
        'WS'=>'SAMOA',
        'SM'=>'SAN MARINO',
        'ST'=>'SAO TOME AND PRINCIPE',
        'SA'=>'SAUDI ARABIA',
        'SN'=>'SENEGAL',
        'SC'=>'SEYCHELLES',
        'SL'=>'SIERRA LEONE',
        'SG'=>'SINGAPORE',
        'SK'=>'SLOVAKIA',
        'SI'=>'SLOVENIA',
        'SB'=>'SOLOMON ISLANDS',
        'SO'=>'SOMALIA',
        'ZA'=>'SOUTH AFRICA',
        'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
        'ES'=>'SPAIN',
        'LK'=>'SRI LANKA',
        'SD'=>'SUDAN',
        'SR'=>'SURINAME',
        'SJ'=>'SVALBARD AND JAN MAYEN',
        'SZ'=>'SWAZILAND',
        'SE'=>'SWEDEN',
        'CH'=>'SWITZERLAND',
        'SY'=>'SYRIAN ARAB REPUBLIC',
        'TW'=>'TAIWAN, PROVINCE OF CHINA',
        'TJ'=>'TAJIKISTAN',
        'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
        'TH'=>'THAILAND',
        'TG'=>'TOGO',
        'TK'=>'TOKELAU',
        'TO'=>'TONGA',
        'TT'=>'TRINIDAD AND TOBAGO',
        'TN'=>'TUNISIA',
        'TR'=>'TURKEY',
        'TM'=>'TURKMENISTAN',
        'TC'=>'TURKS AND CAICOS ISLANDS',
        'TV'=>'TUVALU',
        'UG'=>'UGANDA',
        'UA'=>'UKRAINE',
        'AE'=>'UNITED ARAB EMIRATES',
        'GB'=>'UNITED KINGDOM',
        'US'=>'UNITED STATES',
        'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
        'UY'=>'URUGUAY',
        'UZ'=>'UZBEKISTAN',
        'VU'=>'VANUATU',
        'VE'=>'VENEZUELA',
        'VN'=>'VIET NAM',
        'VG'=>'VIRGIN ISLANDS, BRITISH',
        'VI'=>'VIRGIN ISLANDS, U.S.',
        'WF'=>'WALLIS AND FUTUNA',
        'EH'=>'WESTERN SAHARA',
        'YE'=>'YEMEN',
        'YU'=>'YUGOSLAVIA',
        'ZM'=>'ZAMBIA',
        'ZW'=>'ZIMBABWE',
    );
?>
<form method="post">
<?php
if (isset($_POST['cd']))
{
	if (isset($_POST['rp_tax']))
	{
		$myNewTax = $_POST['rp_tax'];
		update_option('taxCountries', $myNewTax);
	}
	else
	{
		$myNewTax = '';
		update_option('taxCountries', $myNewTax);		
	}
		// Roles
	
	if (isset($_POST['rp_t_roles']))
	{
		$myTaxRoles = $_POST['rp_t_roles'];
		update_option('taxRoles', $myTaxRoles);	
	}
	else
	{
		$myTaxRoles = '';
		update_option('taxRoles', $myTaxRoles);		
	}
}
$myTCountries = get_option('taxCountries');
$myTaxes = get_option('taxRoles');
?>
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">TAX EXEMPTION</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
<select class="chzn-tax" style="width:100%;" multiple="true" name="rp_tax[]" data-placeholder="Select Country(s)">
		<?php
			foreach($country_array as $abbr => $name)
					{
					if (in_array($abbr, $myTCountries))
					{
					?>
					<option value="<?php echo $abbr; ?>" selected="selected"><?php echo ucfirst(strtolower($name)); ?></option>
					<?php
					}
					else
					{
					?>
					<option value="<?php echo $abbr; ?>"><?php echo ucfirst(strtolower($name)); ?></option>
					<?php
					}
			}
		?>
</select>

 <hr />
 <?php
 	global $wp_roles;
    $troles = $wp_roles->get_names();
	?>
 <select class="chzn-roles" style="width:100%;" multiple="true" name="rp_t_roles[]" data-placeholder="Exclude roles">
        <?php
		foreach($troles as $trole)
		{ 
		 	$trolev = str_replace('_', ' ', $trole);
			//$trolev = strtolower($trolev);
					if (in_array($trole, $myTaxes))
					{
					?>
					<option value="<?php echo $trolev; ?>" selected="selected"><?php echo ucfirst($trole); ?></option>
					<?php
					}
					else
					{
					?>
					<option value="<?php echo $trolev; ?>"><?php echo ucfirst($trole); ?></option>
					<?php
					}
		}
		?>
</select>  
<?php submit_button(); ?>
</td>
          </tr> 
          <tr>
          <td><span style="font-style:italic;font-size:12px;">Allow TAX exemption for the selected COUNTRY(S) based on the selected ROLES.</span></td>
          </tr>  
        </tbody>
 </table>
 <input type="hidden" name="cd" value="1" />
 </form>
  </div>
<!-- Tax Countries --> 

<div class="row">
<form method="post" action="options.php">
<?php settings_fields( 'rp_round_field' );?>
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th colspan="2" style="color:#FFF;">FORMAT PRICE</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Show rounded price</strong></td>   
            <td><input name="rp_round_price" type="checkbox" value="1" <?php checked( '1', get_option( 'rp_round_price' ) ); ?>/></td>     
          </tr>
          <tr>
          <td colspan="2" style="font-style:italic;font-size:12px;">Tick the checkbox to enable round price. Example 15.50 will be shown as 16 and 15.40 will be shown as 15 if round price enabled.<?php submit_button(); ?></td>
          </tr>
        </tbody>
 </table>
 </form>
</div>
        <div class="row">
        <form name="rpcreate" id="rpcreate">      
        <?php
		if (isset($_POST['rp_new_role_submit']) && isset($_POST['rp_new_role'] ))
		{
			$new_rp_role = $_POST['rp_new_role'];
			$rp_role_val = str_replace(' ', '_', $new_rp_role);
			$rp_role_val = strtolower($rp_role_val);

		add_role($rp_role_val, $new_rp_role, array(
		'read' => true, 
		'edit_posts' => false,
		'delete_posts' => false, 
		));
		register_setting( 'rp_form_fields', $new_rp_role );
		}
		?>
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">CREATE NEW ROLE <span class="addloading"><img src="<?php echo $plugin_dir ?>assets/add.GIF" /></span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="text" name="rp_new_role" id="rp_new_role" value=""/><br /><br /><br />
				<button type="button" id="role_create" class="btn btn-success btn-sm">Create Role</button>
<div id="ins_result" style="margin-top:15px;"></div>
</td>        
          </tr>  
        </tbody>
 </table>
 </form>
  </div>
<script>
var jQuery = jQuery.noConflict();
jQuery(document).ready(function()
{
	 	jQuery(".chzn-select").chosen();
		jQuery(".chzn-tax").chosen();	
		jQuery(".chzn-roles").chosen();			
	 
jQuery("#rpdelete").click(function(){
    	if(jQuery('#rp_roles').val() == ''){
			jQuery('#del_result').show();
			jQuery('#del_result').text("Please select a ROLE!");
			jQuery('#del_result').fadeOut(5000);
			return false;
		}
		
				var v = jQuery('#rp_roles').val();
				jQuery('#rp_roles option:contains("Select Role")').prop('selected',true);
		
				jQuery.ajax({
						type: "post",url: "admin-ajax.php",data: { action: 'delrolesrp', wrp_role: v, _ajax_nonce: '<?php echo $nonce_2; ?>' },
						beforeSend: function() {jQuery(".delloading").show();},
						complete: function() { jQuery(".delloading").hide();},
						success: function(html){ 
							jQuery("#role_result").html(html); 
							jQuery("#role_result").show("slow"); 
						}
					});	

});
jQuery("#role_create").click(function(){
    	if(jQuery('#rp_new_role').val() == ''){
			jQuery('#ins_result').show();
			jQuery('#ins_result').text("Please enter ROLE NAME!");
			jQuery('#ins_result').fadeOut(5000);
			return false;
		}
		var r = jQuery('#rp_new_role').val();
		jQuery('#rp_new_role').val("");
		
				jQuery.ajax({
						type: "post",url: "admin-ajax.php",data: { action: 'addrolesrp', wrp_role: r, _ajax_nonce: '<?php echo $nonce_1; ?>' },
						beforeSend: function() {jQuery(".addloading").show();}, 
						complete: function() { jQuery(".addloading").hide();}, 
						success: function(html){ 
							jQuery("#role_result").html(html); 
							jQuery("#role_result").show("slow"); 
						}
					});				
			
});
});
</script> 
<div class="row"> 
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">DELETE ROLE <span class="delloading"><img src="<?php echo $plugin_dir ?>assets/del.GIF" /></span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
            
	<select name="rp_roles" id="rp_roles">     
	<?php
	global $wp_roles;
    $roles = $wp_roles->get_names();
    foreach($roles as $role) { 
 	$rolev = str_replace('_', ' ', $role);
	$role = strtolower($role);
	if ($role=='administrator')
	{
		continue;
	}
	?>
 	<option value="<?php echo $rolev; ?>"><?php echo ucfirst($role); ?></option>
	<?php
	} ?>
    <option value="" selected>Select Role</option>
	</select><br /><br /><br />
    <button type="button" id="rpdelete" class="btn btn-danger btn-sm">Delete Role</button>
<div id="del_result" style="margin-top:15px;"></div>
</td>        
          </tr>   
        </tbody>
 </table>
  </div>
  
  <!-- Category -->  
<?php
global $product,$post,$woocommerce;
$args = array(
'post_type' => 'products',
'orderby' => "category"
);
$product_categories = get_terms( 'product_cat', $args );
?>
<div class="row"> 
<form method="post">
<?php
if (isset($_POST['vx']))
{
	if (isset($_POST['rp_teams']))
	{
		$myNewOptions = $_POST['rp_teams'];
		update_option('myCategories', $myNewOptions);
	}
	else
	{
		$myNewOptions = '';
		update_option('myCategories', $myNewOptions);		
	}	
}
$myOptions = get_option('myCategories');

?>
 <table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">EXCLUDE CATEGORY</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
<select class="chzn-select" style="width:100%;" multiple="true" name="rp_teams[]" data-placeholder="Exclude category">
        <?php
		foreach ($product_categories as $cat)
		{
			if (in_array($cat->name, $myOptions))
			{
			?>
			<option value="<?php echo $cat->name; ?>" selected="selected"><?php echo $cat->name; ?></option>
            <?php
			}
			else
			{
			?>
			<option value="<?php echo $cat->name; ?>"><?php echo $cat->name; ?></option>
            <?php
			}
		}
		?>
</select>    
<?php submit_button(); ?>
</td>        
          </tr>   
        </tbody>
 </table>
 <input type="hidden" name="vx" value="1" />
 </form>
  </div>
<!-- Category -->  
     
  </div>
</div>
</div>
