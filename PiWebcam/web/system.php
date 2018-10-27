<?php 
	include "header.php";
	// form submitted
	if(count($_REQUEST) > 0) {
		// system configuration form
		if ($_REQUEST["action"] === "configure_system") {
			save_config(array('DEVICE_NAME','DEVICE_PASSWORD','DEVICE_TIMEZONE','DEVICE_COUNTRY_CODE','DEVICE_LED','DEBUG'));
			run("configure_system");
			load_config();
			array_push($message["success"],"Configuration applied successfully");
		}
		// export configuration form
		if ($_REQUEST["action"] === "export_config") {
			// make the user downloading the configuration file
			$config = run("show_config");
			header("Content-Disposition: attachment; filename=\"PiWebcam.conf\"");
			header("Content-Type: application/force-download");
			header("Content-Length: ".strlen($config));
			header("Connection: close");
			print("$config");
			exit();
		}
		// import configuration form
		if ($_REQUEST["action"] === "import_config") {
			if ($_FILES["file"]["error"] === 0) {
				// import the configuration file
				run("import_config '".$_FILES["file"]["tmp_name"]."'");
				run("configure");
				load_config();
				array_push($message["success"],"Configuration imported successfully");
			} else array_push($message["warning"],"Invalid configuration file provided");
		}
		// upgrade form
		if ($_REQUEST["action"] === "upgrade") {
			if ($_FILES["file"]["error"] === 0) {
				// import the firmware
				run("import_firmware '".$_FILES["file"]["tmp_name"]."'");
				array_push($message["warning"],"The upgrade in progress, the device will reboot once finished.");
			} else array_push($message["danger"],"Invalid firmware provided");
		}
		// reboot form
		if ($_REQUEST["action"] === "reboot") {
			run("reboot");
		}
		// factory reset form
		if ($_REQUEST["action"] === "factory_reset") {
			run("factory_reset");
			run("reboot");
		}
		// data reset form
		if ($_REQUEST["action"] === "data_reset") {
			run("data_reset");
			run("reboot");
		}
	}
	
	// generate the modals
	generate_modal("confirm_modal","If the password has changed, you will need to re-authenticate with the new credentials.<br><br>If the hostname has changed, please consider rebooting the device after applying the new settings.","button","form");
	generate_modal("import_modal","Ensure the configuration file is valid. The device will be rebooted to apply the new settings, please allow 2-3 minutes before reconnecting.","import_button","import_form");
	generate_modal("reboot_modal","The device will be rebooted. Please allow 2-3 minutes before reconnecting.","reboot_button","reboot_form");
	generate_modal("factory_reset_modal","By restoring factory defaults all the current settings will be lost and the camera pictures/movies deleted.<br><br>This operation is irreversible.<br><br>The system will automatically reboot after restoring to its original state.<br>Please allow 2-3 minutes before reconnecting.","factory_reset_button","factory_reset_form");
	generate_modal("data_reset_modal","All camera pictures/movies will be deleted but configuration settings will be kept.<br><br>The system will automatically reboot.<br>Please allow 2-3 minutes before reconnecting.","data_reset_button","data_reset_form");
	generate_modal("upgrade_modal","The device is about to be upgraded.<br><br>The system will reboot to complete the process and install the update.<br>Please allow 3-5 minutes before reconnecting.","upgrade_button","upgrade_form");
	
	include "messages.php";
?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-<?php print $env["MY_WEB_PANEL_STYLE"] ?>">
                        <div class="panel-heading">
                           <i class="fa fa-gears fa-fw"></i> System
                        </div>
                        <div class="panel-body">
                            <div class="row">
								<div class="col-lg-6">
									<div class="panel panel-default">
										<div class="panel-body">
										<h3>Device Settings</h3>
											<form id="form" method="POST" role="form">
											<input type="hidden" name="action" value="configure_system">
												<div class="form-group">
													<label>Name</label>
													<input class="form-control" name="DEVICE_NAME" value="<?php print $config["DEVICE_NAME"] ?>" required>
													<p class="help-block">The name of this device</p>
												</div>
												<div class="form-group">
													<label>Password</label>
													<input class="form-control" name="DEVICE_PASSWORD" type="password" value="<?php print $config["DEVICE_PASSWORD"] ?>">
													<p class="help-block">The same password will be then used for both SSH and web</p>
												</div>
												<div class="form-group">
													<label>Timezone</label>
													<select name="DEVICE_TIMEZONE" class="form-control">		
														<?php
															$timezones = array('','Africa/Lagos','Africa/Porto-Novo','Africa/Juba','Africa/Monrovia','Africa/Bissau','Africa/Ouagadougou','Africa/Khartoum','Africa/Tunis','Africa/Accra','Africa/Windhoek','Africa/Abidjan','Africa/Mbabane','Africa/El_Aaiun','Africa/Niamey','Africa/Luanda','Africa/Kampala','Africa/Lusaka','Africa/Blantyre','Africa/Dakar','Africa/Malabo','Africa/Asmara','Africa/Douala','Africa/Kigali','Africa/Kinshasa','Africa/Maseru','Africa/Dar_es_Salaam','Africa/Nairobi','Africa/Gaborone','Africa/Maputo','Africa/Freetown','Africa/Harare','Africa/Ceuta','Africa/Asmera','Africa/Conakry','Africa/Bujumbura','Africa/Lubumbashi','Africa/Djibouti','Africa/Nouakchott','Africa/Sao_Tome','Africa/Casablanca','Africa/Libreville','Africa/Cairo','Africa/Bangui','Africa/Brazzaville','Africa/Johannesburg','Africa/Timbuktu','Africa/Addis_Ababa','Africa/Banjul','Africa/Bamako','Africa/Lome','Africa/Ndjamena','Africa/Mogadishu','Africa/Algiers','Africa/Tripoli','America/Aruba','America/Fortaleza','America/Miquelon','America/Adak','America/Denver','America/Inuvik','America/Mendoza','America/Fort_Wayne','America/Iqaluit','America/Dominica','America/Mexico_City','America/Godthab','America/Glace_Bay','America/Danmarkshavn','America/Mazatlan','America/Costa_Rica','America/Thunder_Bay','America/Indianapolis','America/Havana','America/Cambridge_Bay','America/Los_Angeles','America/Shiprock','America/Merida','America/Juneau','America/St_Lucia','America/Cordoba','America/Asuncion','America/Port_of_Spain','America/Sao_Paulo','America/Tortola','America/Buenos_Aires','America/Matamoros','America/Guyana','America/Argentina','America/Argentina/ComodRivadavia','America/Argentina/Rio_Gallegos','America/Argentina/Mendoza','America/Argentina/San_Juan','America/Argentina/Cordoba','America/Argentina/Buenos_Aires','America/Argentina/Catamarca','America/Argentina/Salta','America/Argentina/Tucuman','America/Argentina/Jujuy','America/Argentina/Ushuaia','America/Argentina/San_Luis','America/Argentina/La_Rioja','America/Pangnirtung','America/Montreal','America/Whitehorse','America/Monterrey','America/Kralendijk','America/Grand_Turk','America/Resolute','America/Metlakatla','America/Tegucigalpa','America/Thule','America/Dawson_Creek','America/Bahia_Banderas','America/Barbados','America/Atikokan','America/Fort_Nelson','America/New_York','America/Punta_Arenas','America/Blanc-Sablon','America/El_Salvador','America/Noronha','America/Dawson','America/Regina','America/St_Kitts','America/Santo_Domingo','America/Bogota','America/Porto_Velho','America/Yakutat','America/Grenada','America/Swift_Current','America/Atka','America/Santiago','America/Rio_Branco','America/Campo_Grande','America/Eirunepe','America/Boise','America/Guatemala','America/Antigua','America/Panama','America/Knox_IN','America/Boa_Vista','America/Catamarca','America/Jamaica','America/St_Thomas','America/Curacao','America/Montserrat','America/Maceio','America/Caracas','America/Bahia','America/Cayenne','America/Virgin','America/Recife','America/Belem','America/Marigot','America/Martinique','America/Montevideo','America/Cayman','America/Kentucky','America/Kentucky/Monticello','America/Kentucky/Louisville','America/Creston','America/Nassau','America/Chicago','America/Ensenada','America/Winnipeg','America/Guayaquil','America/Rainy_River','America/North_Dakota','America/North_Dakota/New_Salem','America/North_Dakota/Center','America/North_Dakota/Beulah','America/Santa_Isabel','America/Jujuy','America/La_Paz','America/Moncton','America/Toronto','America/Cuiaba','America/Lower_Princes','America/Puerto_Rico','America/Paramaribo','America/St_Johns','America/Louisville','America/Araguaina','America/Sitka','America/Port-au-Prince','America/Chihuahua','America/Guadeloupe','America/Nome','America/Menominee','America/Ojinaga','America/Porto_Acre','America/Coral_Harbour','America/Tijuana','America/Lima','America/Phoenix','America/Vancouver','America/Yellowknife','America/Detroit','America/Cancun','America/St_Vincent','America/Managua','America/Nipigon','America/Halifax','America/Goose_Bay','America/Indiana','America/Indiana/Indianapolis','America/Indiana/Tell_City','America/Indiana/Vevay','America/Indiana/Petersburg','America/Indiana/Winamac','America/Indiana/Knox','America/Indiana/Vincennes','America/Indiana/Marengo','America/Belize','America/St_Barthelemy','America/Anchorage','America/Anguilla','America/Rosario','America/Santarem','America/Hermosillo','America/Rankin_Inlet','America/Manaus','America/Edmonton','America/Scoresbysund','Antarctica/','Antarctica/South_Pole','Antarctica/Syowa','Antarctica/McMurdo','Antarctica/Casey','Antarctica/Vostok','Antarctica/DumontDUrville','Antarctica/Mawson','Antarctica/Rothera','Antarctica/Palmer','Antarctica/Macquarie','Antarctica/Davis','Antarctica/Troll','Australia/LHI','Australia/Tasmania','Australia/Lord_Howe','Australia/Canberra','Australia/Hobart','Australia/Darwin','Australia/Melbourne','Australia/South','Australia/Perth','Australia/Adelaide','Australia/ACT','Australia/North','Australia/Broken_Hill','Australia/Queensland','Australia/Eucla','Australia/Brisbane','Australia/NSW','Australia/Victoria','Australia/West','Australia/Sydney','Australia/Currie','Australia/Yancowinna','Australia/Lindeman','Arctic/Longyearbyen','Asia/Vientiane','Asia/Macao','Asia/Bahrain','Asia/Famagusta','Asia/Almaty','Asia/Tehran','Asia/Omsk','Asia/Aqtobe','Asia/Rangoon','Asia/Kuching','Asia/Qatar','Asia/Makassar','Asia/Vladivostok','Asia/Khandyga','Asia/Seoul','Asia/Urumqi','Asia/Yakutsk','Asia/Ulaanbaatar','Asia/Kuwait','Asia/Kamchatka','Asia/Srednekolymsk','Asia/Ujung_Pandang','Asia/Hong_Kong','Asia/Hebron','Asia/Pontianak','Asia/Karachi','Asia/Aqtau','Asia/Kashgar','Asia/Ust-Nera','Asia/Calcutta','Asia/Nicosia','Asia/Harbin','Asia/Samarkand','Asia/Taipei','Asia/Beirut','Asia/Chungking','Asia/Muscat','Asia/Bishkek','Asia/Ashkhabad','Asia/Atyrau','Asia/Phnom_Penh','Asia/Amman','Asia/Magadan','Asia/Ashgabat','Asia/Jakarta','Asia/Macau','Asia/Thimphu','Asia/Kuala_Lumpur','Asia/Aden','Asia/Istanbul','Asia/Dili','Asia/Katmandu','Asia/Jerusalem','Asia/Bangkok','Asia/Pyongyang','Asia/Baghdad','Asia/Hovd','Asia/Irkutsk','Asia/Jayapura','Asia/Yerevan','Asia/Sakhalin','Asia/Tokyo','Asia/Chita','Asia/Choibalsan','Asia/Riyadh','Asia/Gaza','Asia/Barnaul','Asia/Colombo','Asia/Dubai','Asia/Manila','Asia/Yekaterinburg','Asia/Tel_Aviv','Asia/Thimbu','Asia/Dhaka','Asia/Tbilisi','Asia/Novokuznetsk','Asia/Qyzylorda','Asia/Ulan_Bator','Asia/Yangon','Asia/Kathmandu','Asia/Anadyr','Asia/Saigon','Asia/Chongqing','Asia/Singapore','Asia/Novosibirsk','Asia/Ho_Chi_Minh','Asia/Dacca','Asia/Oral','Asia/Damascus','Asia/Brunei','Asia/Kolkata','Asia/Shanghai','Asia/Kabul','Asia/Baku','Asia/Tomsk','Asia/Krasnoyarsk','Asia/Dushanbe','Asia/Tashkent','Atlantic/St_Helena','Atlantic/Madeira','Atlantic/Bermuda','Atlantic/Stanley','Atlantic/Faeroe','Atlantic/Canary','Atlantic/Jan_Mayen','Atlantic/Reykjavik','Atlantic/South_Georgia','Atlantic/Azores','Atlantic/Faroe','Atlantic/Cape_Verde','Europe/Belgrade','Europe/Mariehamn','Europe/Madrid','Europe/Paris','Europe/Belfast','Europe/Ulyanovsk','Europe/Kiev','Europe/Nicosia','Europe/Vatican','Europe/Brussels','Europe/Lisbon','Europe/Malta','Europe/Busingen','Europe/Zagreb','Europe/Vienna','Europe/Bratislava','Europe/San_Marino','Europe/Uzhgorod','Europe/Sofia','Europe/London','Europe/Amsterdam','Europe/Istanbul','Europe/Stockholm','Europe/Andorra','Europe/Kaliningrad','Europe/Athens','Europe/Kirov','Europe/Zaporozhye','Europe/Saratov','Europe/Skopje','Europe/Luxembourg','Europe/Dublin','Europe/Berlin','Europe/Jersey','Europe/Warsaw','Europe/Bucharest','Europe/Podgorica','Europe/Isle_of_Man','Europe/Monaco','Europe/Minsk','Europe/Budapest','Europe/Oslo','Europe/Copenhagen','Europe/Gibraltar','Europe/Sarajevo','Europe/Riga','Europe/Moscow','Europe/Tirane','Europe/Vaduz','Europe/Guernsey','Europe/Astrakhan','Europe/Helsinki','Europe/Tallinn','Europe/Zurich','Europe/Chisinau','Europe/Rome','Europe/Ljubljana','Europe/Simferopol','Europe/Volgograd','Europe/Prague','Europe/Vilnius','Europe/Samara','Europe/Tiraspol','Indian/Maldives','Indian/Chagos','Indian/Mahe','Indian/Antananarivo','Indian/Kerguelen','Indian/Cocos','Indian/Mauritius','Indian/Christmas','Indian/Reunion','Indian/Mayotte','Indian/Comoro','Pacific/Truk','Pacific/Funafuti','Pacific/Efate','Pacific/Auckland','Pacific/Port_Moresby','Pacific/Rarotonga','Pacific/Johnston','Pacific/Guam','Pacific/Wallis','Pacific/Norfolk','Pacific/Fiji','Pacific/Gambier','Pacific/Kosrae','Pacific/Wake','Pacific/Easter','Pacific/Pohnpei','Pacific/Tongatapu','Pacific/Noumea','Pacific/Ponape','Pacific/Galapagos','Pacific/Nauru','Pacific/Chatham','Pacific/Chuuk','Pacific/Niue','Pacific/Kwajalein','Pacific/Enderbury','Pacific/Samoa','Pacific/Apia','Pacific/Midway','Pacific/Majuro','Pacific/Marquesas','Pacific/Bougainville','Pacific/Tahiti','Pacific/Pitcairn','Pacific/Kiritimati','Pacific/Honolulu','Pacific/Yap','Pacific/Fakaofo','Pacific/Pago_Pago','Pacific/Saipan','Pacific/Tarawa','Pacific/Guadalcanal','Pacific/Palau','US/East-Indiana','US/Hawaii','US/Central','US/Arizona','US/Mountain','US/Michigan','US/Alaska','US/Samoa','US/Eastern','US/Aleutian','US/Pacific-New','US/Pacific','US/Indiana-Starke');
															sort($timezones);
															foreach ($timezones as $index => $timezone) {
																print "<option value=\"".$timezone."\"";
																if ($config["DEVICE_TIMEZONE"] === $timezone) print " selected"; 
																print ">$timezone</option>\n";
															}
														?>
													</select>
												<p class="help-block">The timezone will be used for displaying the right time</p>
												</div>
												<div class="form-group">
													<label>Country Code</label>
													<select name="DEVICE_COUNTRY_CODE" class="form-control">
														<?php
															$countries = array('','AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BV','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM','PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','UM','US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','YE','YT','ZA','ZM','ZW');
															sort($countries);
															foreach ($countries as $index => $country) {
																print "<option value=\"".$country."\"";
																if ($config["DEVICE_COUNTRY_CODE"] === $country) print " selected"; 
																print ">$country</option>\n";
															}
														?>
													</select>
													<p class="help-block">The country will be used for connecting to the WiFi</p>
												</div>
												<label>LEDs</label>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="DEVICE_LED">
														<input value="1" name="DEVICE_LED" type="checkbox"<?php if ($config["DEVICE_LED"] === "1") print " checked"; ?>> Enable LEDs
													</label>
												</div>
												<p class="help-block">If uncheched both the red and the green leds on the board will be turned off (<i>default: checked</i>).</p>
												<label>Debug</label>
												<div class="checkbox">
													<label>
														<input type='hidden' value="0" name="DEBUG">
														<input value="1" name="DEBUG" type="checkbox"<?php if ($config["DEBUG"] === "1") print " checked"; ?>> Enable debug
													</label>
												</div>
												<p class="help-block">Increase the verbosity and print out debug messages for troubleshooting (<i>default: unchecked</i>).</p>
												<button id="button" type="submit" onclick='$("#button").addClass("disabled"); $("#confirm_modal").modal("show");return false;' class="pull-right btn btn-primary">Apply settings</button>
											</form>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="panel panel-default">
										<div class="panel-body">
										<h3>Configuration</h3>
											<form method="POST" role="form">
											<input type="hidden" name="action" value="export_config">
											<input type="hidden" name="no_headers" value="1">
												<label>Export configuration</label>
												<div class="form-group">
													<button type="submit" class="btn btn-outline btn-primary">Export</button>
													<p class="help-block">Click to export the settings of this device</p>
												</div>
											</form>
											<form id="import_form" method="POST" role="form" enctype="multipart/form-data">
											<input type="hidden" name="action" value="import_config">
												<label>Import configuration</label>
												<div class="form-group">
													<input name="file" type="file">
												</div>
												<div class="form-group">
													<button id="import_button" type="submit" onclick='$("#import_button").addClass("disabled"); $("#import_modal").modal("show");return false;' class="btn btn-outline btn-primary" >Import</button>
													<p class="help-block">Select the new firmware from your computer and click to import</p>
												</div>
											</form>
										</div>
									</div>
									<div class="panel panel-default">
										<div class="panel-body">
										<h3>System Upgrade</h3>
											<form id="upgrade_form" method="POST" role="form" enctype="multipart/form-data">
											<input type="hidden" name="action" value="upgrade">
												<label>Upgrade Device</label>
												<div class="form-group">
													<input name="file" type="file">
												</div>
												<div class="form-group">
													<button id="upgrade_button" type="submit" onclick='$("#upgrade_button").addClass("disabled"); $("#upgrade_modal").modal("show");return false;' class="btn btn-outline btn-primary" >Upgrade</button>
													<p class="help-block">Select an update file from your computer and click to upgrade the firmware of this device</p>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="panel panel-default">
										<div class="panel-body">
										<center><h3>Device Management</h3></center>
											<div class="row">
												<div class="col-lg-4"><center>
													<form id="reboot_form" method="POST" role="form">
													<input type="hidden" name="action" value="reboot">
														<button id="reboot_button" type="submit" onclick='$("#reboot_button").addClass("disabled"); $("#reboot_modal").modal("show");return false;' class="btn btn-info">Reboot</button>
													</form></center>
												</div>
												<div class="col-lg-4"><center>
													<form id="data_reset_form" method="POST" role="form">
													<input type="hidden" name="action" value="data_reset">
														<button id="data_reset_button" type="submit" onclick='$("#data_reset_button").addClass("disabled"); $("#data_reset_modal").modal("show");return false;' class="btn btn-warning">Data Reset</button>
													</form></center>
												</div>
												<div class="col-lg-4"><center>
													<form id="factory_reset_form" method="POST" role="form">
													<input type="hidden" name="action" value="factory_reset">
														<button id="factory_reset_button" type="submit" onclick='$("#factory_reset_button").addClass("disabled"); $("#factory_reset_modal").modal("show");return false;' class="btn btn-danger">Factory Reset</button>
													</form></center>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php 
	include "footer.php" 
?>