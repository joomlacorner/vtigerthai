<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  crm-now, www.crm-now.com
* Portions created by crm-now are Copyright (C)  crm-now c/o im-netz Neue Medien GmbH.
* All Rights Reserved.
 *
 ********************************************************************************/
function createpdffile ($idnumber,$purpose='', $path='',$current_id='') {
	require_once('include/tcpdf/tcpdf.php');
	require_once('include/tcpdf/config/tcpdf_config.php');
	require_once('modules/Invoice/Invoice.php');
	require_once('include/database/PearDatabase.php');
	require_once('include/utils/InventoryUtils.php');
	require_once('include/utils/PDFutils.php');
	require_once('test/contact/bank.php');
	global $FOOTER_PAGE, $default_font, $font_size_footer, $NUM_FACTURE_NAME, $pdf_strings, $quote_no, $footer_margin;
	global $org_name, $org_address, $org_city, $org_code, $org_country, $org_irs, $org_taxid, $org_phone, $org_fax, $org_website;
	global $ORG_POSITION,$VAR_PAGE, $VAR_OF, $invoice_status;
	//bank information - content
	global $bank_name , $bank_street , $bank_city ,$bank_zip ,$bank_country, $bank_account, $bank_routing, $bank_iban, $bank_swift;
	//bank information - labels from language files
	global $ACCOUNT_NUMBER, $ROUTING_NUMBER, $SWIFT_NUMBER, $IBAN_NUMBER;
	global $columns, $logoradio, $logo_name, $footerradio, $pageradio;
	global $adb,$app_strings,$focus,$current_user,$invoice_no, $purposefooter;
	$module = 'Invoice';
	//get bank information
	$bank_name = $bank_array['bank_name'];
	$bank_street = $bank_array['bank_street'];
	$bank_city = $bank_array['bank_city'];
	$bank_zip = $bank_array['bank_zip'];
	$bank_country = $bank_array['bank_country'];
	$bank_account = $bank_array['bank_account'];
	$bank_routing = $bank_array['bank_routing'];
	$bank_iban = $bank_array['bank_iban'];
	$bank_swift = $bank_array['bank_swift'];
	//get tax information
	$org_taxid = $tax_array['org_taxid'];
	$org_irs = $tax_array['org_irs'];

	//get the stored configuration values
	$pdf_config_details = getAllPDFDetails('Invoice');
	//set font
	$default_font = getTCPDFFontsname ($pdf_config_details[fontid]);
	if ($default_font =='') $default_font = 'freesans';
	$font_size_header = $pdf_config_details[fontsizeheader];
	$font_size_address = $pdf_config_details[fontsizeaddress];
	$font_size_body = $pdf_config_details[fontsizebody];
	$font_size_footer = $pdf_config_details[fontsizefooter];

	//select comma or dot as numberformat
	//European Format
	$decimal_precision = 2;
	$decimals_separator = ',';
	$thousands_separator = '.';
	//US Format
	//$decimal_precision = 2;
	//$decimals_separator = '.';
	//$thousands_separator = ',';

	//get users data
	//select language file
	if (file_exists("modules/Invoice/language/".$pdf_config_details[pdflang].".lang.pdf.php")) {
		include("modules/Invoice/language/".$pdf_config_details[pdflang].".lang.pdf.php");
		$language = strtoupper(substr($pdf_config_details[pdflang], -2, 2));
	} 
	else {
		include("modules/Invoice/language/en_us.lang.pdf.php");
		$language = "EN";
	}
	//footer: store purpose
	$purposefooter = $purpose;
	
	//internal number
	$id = $idnumber;

	//retreiving the Invoice  info
	$focus = new Invoice();
	$focus->retrieve_entity_info($id,"Invoice");
	$account_name = decode_html(getAccountName($focus->column_fields['account_id']));
	$invoice_no = $focus->column_fields['invoice_no'];
	$sql="select vtiger_currency_info.currency_symbol from vtiger_currency_info where vtiger_currency_info.id= ".$focus->column_fields['currency_id'];
	$currency_symbol = $adb->query_result($adb->query($sql),0,'currency_symbol');

	// credit note?
	$invoice_status = $focus->column_fields["invoicestatus"];

	// get invoice date
	$invoice_date = $focus->column_fields["invoicedate"];
	$invoice_date = getDisplayDate($invoice_date);
	$invoice_date = str_replace ("-",".",$invoice_date);

	//number of lines after headline
	$space_headline = $pdf_config_details[space_headline];

	//display logo?
	$logoradio = $pdf_config_details[logoradio];

	//get PO name
	$poname = $pdf_config_details['poname'];

	// get client id
	$clientid = $pdf_config_details['clientid'];

	//display summary?
	$summaryradio = $pdf_config_details[summaryradio];

	//display footer?
	$footerradio = $pdf_config_details[footerradio];
	//display footer page number?
	$pageradio = $pdf_config_details[pageradio];
	// get company information from settings
	$add_query = "select * from vtiger_organizationdetails";
	$result = $adb->query($add_query);
	$num_rows = $adb->num_rows($result);

	if($num_rows > 0)
	{
		$org_name = $adb->query_result($result,0,"organizationname");
		$org_address = $adb->query_result($result,0,"address");
		$org_city = $adb->query_result($result,0,"city");
		$org_state = $adb->query_result($result,0,"state");
		$org_country = $adb->query_result($result,0,"country");
		$org_code = $adb->query_result($result,0,"code");
		$org_phone = $adb->query_result($result,0,"phone");
		$org_fax = $adb->query_result($result,0,"fax");
		$org_website = $adb->query_result($result,0,"website");

		$logo_name = $adb->query_result($result,0,"logoname");
	}
	// get owner information
	$recordOwnerArr = getRecordOwnerId ($_REQUEST['record']);
	foreach($recordOwnerArr as $type=>$id)
	{
		$ownertype=$type;
		$ownerid=$id;
	}
	if($ownertype == 'Users')
	{
		// get owner information for user
		$sql="SELECT * FROM vtiger_users,vtiger_crmentity WHERE vtiger_users.id = vtiger_crmentity.smownerid AND vtiger_crmentity.crmid = '".$_REQUEST['record']."'";
		$result = $adb->query($sql);
		$owner_lastname = $adb->query_result($result,0,'last_name');
		$owner_firstname = $adb->query_result($result,0,'first_name');
		$owner_id = $adb->query_result($result,0,'smownerid');
		$owner_phone = $adb->query_result($result,0,'phone_work');
		$owner_title = decode_html(trim($adb->query_result($result,0,'title')));
	}
	else {
	// get owner information for Groups
		$sql="SELECT * FROM vtiger_groups,vtiger_crmentity WHERE vtiger_groups.groupid  = vtiger_crmentity.smownerid AND vtiger_crmentity.crmid = '".$_REQUEST['record']."'";
		$result = $adb->query($sql);
		$owner_lastname = '';
		$owner_firstname = $adb->query_result($result,0,'groupname');
		$owner_id = $adb->query_result($result,0,'smownerid');
		$owner_phone = $org_phone;
		$owner_title = '';
	}
	//display owner?
	$owner = $pdf_config_details['owner'];
	//display owner phone#?
	$ownerphone = $pdf_config_details['ownerphone'];
	//to display at product description based on tax type
	$gproddetailarray = array($pdf_config_details[gprodname],$pdf_config_details[gproddes],$pdf_config_details[gprodcom]);
	$gproddetails = 0;
	foreach($gproddetailarray as $key=>$value){
		if ($value=='true') {
			if ($key==0) $gproddetails = $gproddetails + 1;
			else $gproddetails = $gproddetails + $key*2;
		}
	}
	$iproddetails = 0;
	$iproddetailarray = array($pdf_config_details[iprodname],$pdf_config_details[iproddes],$pdf_config_details[iprodcom]);
	foreach($iproddetailarray as $key=>$value){
		if ($value=='true') {
			if ($key==0) $iproddetails = $iproddetails + 1;
			else $iproddetails = $iproddetails + $key*2;
		}
	}

	// SO Requisition Nummer 
	$requisition_no = $focus->column_fields['vtiger_purchaseorder'];
	// CustomerMark 
	$customermark = $focus->column_fields['customerno'];
	// get related Sales Order infomation (modified time)
	$salesorder_id = $focus->column_fields['salesorder_id'];

	if($focus->column_fields["hdnTaxType"] == "individual") {
	        $product_taxes = 'true';
	} else {
	        $product_taxes = 'false';
	}

	// **************** BEGIN POPULATE DATA ********************
	$account_id = $focus->column_fields[account_id];

	$valid_till = $focus->column_fields["duedate"];
	$valid_till = getDisplayDate($valid_till); 
	$valid_till = str_replace ("-",".",$valid_till);

	$bill_street = decode_html($focus->column_fields["bill_street"]);
	$bill_city = decode_html($focus->column_fields["bill_city"]);
	$bill_state = decode_html($focus->column_fields["bill_state"]);
	$bill_code = decode_html($focus->column_fields["bill_code"]);
	$bill_country = decode_html($focus->column_fields["bill_country"]);

	//format contact name
	$contact_name =decode_html(getContactforPDF($focus->column_fields["contact_id"]));
	//get department of contact or account, contact wins
	$contact_department = '';
	//get contact department
	if(trim($focus->column_fields["contact_id"]) != '')
	{
        	$sql = "select * from vtiger_contactdetails where contactid=".$focus->column_fields["contact_id"];
        	$result = $adb->query($sql);
        	$contact_department = decode_html(trim($adb->query_result($result,0,"department")));
	        $contact_firstname = decode_html(trim($adb->query_result($result,0,"firstname")));
	        $contact_lastname = decode_html(trim($adb->query_result($result,0,"lastname")));
	        $contact_salutation = decode_html(trim($adb->query_result($result,0,"salutation")));
			
	}
	//get account department
	if($contact_department ==''){
        	$sql = "select * from vtiger_account where accountid=".$account_id;
        	$result = $adb->query($sql);
        	$contact_department = decode_html(trim($adb->query_result($result,0,"tickersymbol")));
	}
		
	$ship_street = $focus->column_fields["ship_street"];
	$ship_city = $focus->column_fields["ship_city"];
	$ship_state = $focus->column_fields["ship_state"];
	$ship_code = $focus->column_fields["ship_code"];
	$ship_country = $focus->column_fields["ship_country"];

	// condition field for last page
	$conditions = decode_html($focus->column_fields["terms_conditions"]);
	// description field for first page
	$description = decode_html($focus->column_fields["description"]);

	// ************************ BEGIN POPULATE DATA ***************************
	//get the Associated Products for this Invoice
	$focus->id = $focus->column_fields["record_id"];
	$associated_products = getAssociatedProducts("Invoice",$focus);
	$num_products = count($associated_products);

	//This $final_details array will contain the final total, discount, Group Tax, S&H charge, S&H taxes and adjustment
	$final_details = $associated_products[1]['final_details'];

	//getting the Net Total
	$price_subtotal = $final_details["hdnSubTotal"];
	$price_subtotal_formated = number_format($price_subtotal,$decimal_precision,$decimals_separator,$thousands_separator);

	//Final discount amount/percentage
	$discount_amount = $final_details["discount_amount_final"];
	$discount_percent = $final_details["discount_percentage_final"];

	if($discount_amount != "") 
		{
		$price_discount = $discount_amount;
		$price_discount_formated = number_format($price_discount,$decimal_precision,$decimals_separator,$thousands_separator);
		}
	else if($discount_percent != "")
	{
		//This will be displayed near Discount label 
		$final_price_discount_percent = "(".number_format($discount_percent,$decimal_precision,$decimals_separator,$thousands_separator)." %)";
		$price_discount = ($discount_percent*$final_details["hdnSubTotal"])/100;
		$price_discount_formated = number_format($price_discount,$decimal_precision,$decimals_separator,$thousands_separator);
	}
	else
	{
		$price_discount = "0.00";
	}
	//Adjustment
	$price_adjustment = $final_details["adjustment"];
	$price_adjustment_formated = number_format($price_adjustment,$decimal_precision,$decimals_separator,$thousands_separator);
	//Grand Total
	$price_total = $final_details["grandTotal"];
	$price_total_formated = number_format($price_total,$decimal_precision,$decimals_separator,$thousands_separator);

	//To calculate the group tax amount
	if($final_details['taxtype'] == 'group')
	{
		$group_tax_total = $final_details['tax_totalamount'];
		$price_salestax = $group_tax_total;
		$price_salestax_formated = number_format($price_salestax,$decimal_precision,$decimals_separator,$thousands_separator);

		$group_total_tax_percent = '0.00';
		$group_tax_details = $final_details['taxes'];
		for($i=0;$i<count($group_tax_details);$i++)
		{
			$group_total_tax_percent = $group_total_tax_percent+$group_tax_details[$i]['percentage'];
		}
	}

	//S&H amount
	$sh_amount = $final_details['shipping_handling_charge'];
	$price_shipping_formated = number_format($sh_amount,$decimal_precision,$decimals_separator,$thousands_separator);

	//S&H taxes
	$sh_tax_details = $final_details['sh_taxes'];
	$sh_tax_percent = '0.00';
	for($i=0;$i<count($sh_tax_details);$i++)
	{
		$sh_tax_percent = $sh_tax_percent + $sh_tax_details[$i]['percentage'];
	}
	$sh_tax_amount = $final_details['shtax_totalamount'];
	$price_shipping_tax = number_format($sh_tax_amount,$decimal_precision,$decimals_separator,$thousands_separator);

	//to calculate the individuel tax amounts included we should get all available taxes and then retrieve the corresponding tax values
	$tax_details = getAllTaxes('available');
	$numer_of_tax_types = count($tax_details);
	for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
	{
		$taxtype_listings[taxname.$tax_count] = $tax_details[$tax_count]['taxname'];
		$taxtype_listings[percentage.$tax_count] = $tax_details[$tax_count]['percentage'];
		$taxtype_listings[value.$tax_count] = '0';
	}
	//This is to get all prodcut details as row basis
	for($i=1,$j=$i-1;$i<=$num_products;$i++,$j++)
	{
		$product_code[$i] = $associated_products[$i]['hdnProductcode'.$i];
		$product_name[$i] = decode_html($associated_products[$i]['productName'.$i]);
		$prod_description[$i] = decode_html($associated_products[$i]['productDescription'.$i]);
		$qty[$i] = $associated_products[$i]['qty'.$i];
		$qty_formated[$i] = number_format($associated_products[$i]['qty'.$i],$decimal_precision,$decimals_separator,$thousands_separator);
		$comment[$i] = decode_html($associated_products[$i]['comment'.$i]);
		$unit_price[$i] = number_format($associated_products[$i]['unitPrice'.$i],$decimal_precision,$decimals_separator,$thousands_separator);
		$list_price[$i] = number_format($associated_products[$i]['listPrice'.$i],$decimal_precision,$decimals_separator,$thousands_separator);
		$list_pricet[$i] = $associated_products[$i]['listPrice'.$i];
		$discount_total[$i] = $associated_products[$i]['discountTotal'.$i];
		$discount_totalformated[$i] = number_format($associated_products[$i]['discountTotal'.$i],$decimal_precision,$decimals_separator,$thousands_separator);
		//added by crm-now
		$usageunit[$i] = $associated_products[$i]['usageunit'.$i];
		//look whether the entry already exists, if the translated string is available then the translated string other wise original string will be returned
		$usageunit[$i] = getTranslatedString($usageunit[$i], 'Products');
		$taxable_total = $qty[$i]*$list_pricet[$i]-$discount_total[$i];

		$producttotal = $taxable_total;
		$total_taxes = '0.00';
		
		
		if($focus->column_fields["hdnTaxType"] == "individual")
		{
			$total_tax_percent = '0.00';
			//This loop is to get all tax percentage and then calculate the total of all taxes
			for($tax_count=0;$tax_count<count($associated_products[$i]['taxes']);$tax_count++)
			{
				$tax_percent = $associated_products[$i]['taxes'][$tax_count]['percentage'];
				$total_tax_percent = $total_tax_percent+$tax_percent;
				$tax_amount = (($taxable_total*$tax_percent)/100);
				//calculate the tax amount for any available tax percentage
				$detected_tax = substr(array_search ($total_tax_percent,$taxtype_listings), -1);
				$taxtype_listings [value.$detected_tax] = $taxtype_listings [value.$detected_tax]+$tax_amount;
				$total_taxes = $total_taxes+$tax_amount;
			}
			$producttotal = $taxable_total+$total_taxes;
			$product_line[$j][$pdf_strings['Tax']] = " ($total_tax_percent %) ".number_format($total_taxes,$decimal_precision,$decimals_separator,$thousands_separator);
		    // combine product name, description and comment to one field based on settings
		}

	    // combine product name, description and comment to one field based on settings
		if($focus->column_fields["hdnTaxType"] == "individual") $product_selection = $iproddetails;
		else $product_selection = $gproddetails;
			switch($product_selection)
			{
			    case 1:
						$product_name_long[$i] = $product_name[$i];
			    break;
			    case 2:
						$product_name_long[$i] = $prod_description[$i];
			    break;
			    case 3:
						$product_name_long[$i] = $product_name[$i]."\n".$prod_description[$i];
			    break;
			    case 4:
						$product_name_long[$i] = $comment[$i];
			    break;
			    case 5:
						$product_name_long[$i] = $product_name[$i]."\n".$comment[$i];
			    break;
			    case 6:
					if ($prod_description[$i]!=''){
						$product_name_long[$i] = $prod_description[$i]."\n".$comment[$i];
						}
					else
						$product_name_long[$i] = $comment[$i];
			    break;
			    case 7:
					if ($prod_description[$i]!=''){
						$product_name_long[$i] = $product_name[$i]."\n".$prod_description[$i]."\n".$comment[$i];
						}
					else
						$product_name_long[$i] = $product_name[$i]."\n".$comment[$i];
			    break;
			    default:
					if ($prod_description[$i]!=''){
						$product_name_long[$i] = $product_name[$i]."\n".$prod_description[$i]."\n".$comment[$i];
						}
					else
						$product_name_long[$i] = $product_name[$i]."\n".$comment[$i];
			    break;
			}

		$prod_total[$i] = number_format($producttotal,$decimal_precision,$decimals_separator,$thousands_separator);

		$product_line[$j][$pdf_strings['Position']] = $j+1;
		$product_line[$j][$pdf_strings['OrderCode']] = $product_code[$i];
		$product_line[$j][$pdf_strings['Description']] = $product_name_long[$i];
		$product_line[$j][$pdf_strings['Qty']] = $qty_formated[$i];
		$product_line[$j][$pdf_strings['Unit']] = $usageunit[$i];
		$product_line[$j][$pdf_strings['UnitPrice']] = $list_price[$i];
		$product_line[$j][$pdf_strings['Discount']] = $discount_totalformated[$i];
		$product_line[$j][$pdf_strings['LineTotal']] = $prod_total[$i];

	}

	//Population of current date
	$addyear = strtotime("+0 year");
	if ($purpose=='customerportal')
	$dat_fmt = (($owner_id->date_format == '')?('dd-mm-yyyy'):($owner_id->date_format));
	else
	$dat_fmt = (($current_user->date_format == '')?('dd-mm-yyyy'):($current_user->date_format));
	$date_issued = (($dat_fmt == 'dd-mm-yyyy')?(date('d-m-Y',$addyear)):(($dat_fmt == 'mm-dd-yyyy')?(date('m-d-Y',$addyear)):(($dat_fmt == 'yyyy-mm-dd')?(date('Y-m-d', $addyear)):(''))));
	
	//special output for shippig note
	if ($purpose=='printsn'){
	// no summary
	$summaryradio = 'false';
	// no description
	//no
	}
	// ************************ END POPULATE DATA ***************************
	//************************BEGIN PDF FORMATING**************************
	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF 
	{
		//modifiy tcpdf class footer
		public function Footer() 
		{
			//To make the function Footer() work properly
			$this->AliasNbPages();

			if (!isset($this->original_lMargin)) 
			{
				$this->original_lMargin = $this->lMargin;
			}
			if (!isset($this->original_rMargin)) 
			{
				$this->original_rMargin = $this->rMargin;
			}
			include("modules/Invoice/pdf_templates/footer.php");
		}
	}
	$page_num='1';
	// create new PDF document
	//$pdf = new PDF( 'P', 'mm', 'A4' );
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 
	// set font
	$pdf->SetFont($default_font, " ", $default_font_size);
	$pdf->setPrintHeader(0); //header switched off permanently
	// auto break on
	//$pdf->SetAutoPageBreak(true); 
	// set footer fonts
	//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	// set pdf information
	if ($invoice_status=='Credit Invoice') $doc_name= 'CREDIT';
	else  $doc_name= 'FACTURE';
	if ($purpose=='printsn') $doc_name= 'SALESNOTE';
	$pdf-> SetTitle ($pdf_strings[$doc_name].": ".$account_name);
	$pdf-> SetAuthor ($owner_firstname." ".$owner_lastname.", ".$org_name);
	$pdf-> SetSubject ($account_name);
	$pdf-> SetCreator ('PDF Creator from www.crm-now.com');
	//list product names as keywords
	$productlisting = implode(", ",$product_name);
	$pdf-> SetKeywords ($productlisting);

	//Disable automatic page break
	$pdf->SetAutoPageBreak(true,PDF_MARGIN_FOOTER);
	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
	//set some language-dependent strings
	$pdf->setLanguageArray($l); 
	//initialize document
	$pdf->AliasNbPages();
	//in reference to body.php -> if a new page must be added if the space available for summary is too small
	$new_page_started = false;
	$pdf->AddPage();
	$pdf-> setImageScale(1.5);
	//$pdf->SetY(PDF_MARGIN_HEADER);
	include("modules/Invoice/pdf_templates/header.php");
	$pdf->SetFont($default_font, " ", $font_size_body);
	include("modules/Invoice/pdf_templates/body.php");

	// issue pdf
	if ($purpose=='print' || $purpose=='printsn'){
	$pdf->Output($pdf_strings[$doc_name].'_'.$date_issued.'.pdf','D'); 
		$pdf->Output($pdf_strings[$doc_name].'_'.$date_issued.'.pdf','D'); 
		exit;
	}
	elseif ($purpose=='send'){
	// send pdf with mail
		switch($language)
		{
			case "EN":
				$pdf->Output('storage/Invoice_'.$_REQUEST['record'].'.pdf','F'); //added file name to make it work in IE, also forces the download giving the user the option to save
				break;
			case "DE":
				$pdf->Output('storage/Rechnung_'.$_REQUEST['record'].'.pdf','F'); //added file name to make it work in IE, also forces the download giving the user the option to save
				break;
		}
		return;
		exit;
	}
	elseif ($purpose=='customerportal')
		$pdf->Output($path.$current_id."_Invoice.pdf",'F'); 
}
?>