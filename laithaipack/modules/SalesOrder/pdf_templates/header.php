<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  crm-now, www.crm-now.com
* Portions created by crm-now are Copyright (C)  crm-now c/o im-netz Neue Medien GmbH.
* All Rights Reserved.
 *
 ********************************************************************************/
$pdf-> setImageScale(1.5);
// ************** Begin company information *****************
//company logo
//function to scal the image to the space availabel is needed
global $logo_name;
if ($logoradio =='true') {
	if (file_exists('test/logo/'.$logo_name))
	$pdf->Image('test/logo/'.$logo_name, $x='125', $y='10', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false);
	else {
	$pdf->SetXY('145','10');
	$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['MISSING_IMAGE'],0,0);
	}
}
// ************** End company information *******************

// ************* Begin Top-Right Header ***************
//set location
$xmargin = '130';
$ymargin = '45';
$xdistance = '40';
$pdf->SetXY($xmargin,$ymargin);
// define standards
$pdf->SetFont($default_font,'',$font_size_header);
// so number-label
$pdf->text($xmargin,$ymargin,$pdf_strings['NUM_FACTURE_NAME']);
//so number-content
//we get the SO # from the entry field, if not set the record id is used
if (trim($requisition_no) != '') $pdf->text($xmargin+$xdistance,$ymargin,$requisition_no);
else $pdf->text($xmargin+$xdistance,$ymargin,$SalesOrder_no);
//so date
$pdf->SetFont($default_font,'',$font_size_header);
//so date - label
$pdf->text($xmargin,$ymargin+5,$pdf_strings['DATE']);
//so date -content
$pdf->text($xmargin+$xdistance,$ymargin+5,$date_to_display);
//delivery date
$pdf->SetFont($default_font,'',$font_size_header);
//so delivery - label
$pdf->text($xmargin,$ymargin+10,$pdf_strings['SODATE']);
//so delivery -content
$pdf->text($xmargin+$xdistance,$ymargin+10,$delivery_date);

//print owner if requested
if ($owner =='true'){
	//owner label
	$pdf->text($xmargin,$ymargin+15,$pdf_strings['ISSUER']);
	//owner-content
	$pdf->text($xmargin+$xdistance,$ymargin+15,$owner_firstname.' '.$owner_lastname);
}
if ($ownerphone =='true'){
	//owner label
	$pdf->text($xmargin,$ymargin+20,$pdf_strings['PHONE']);
	//owner-content
	$pdf->text($xmargin+$xdistance,$ymargin+20,$owner_phone);
}
//print customer markif set
if ($clientid =='true'){
	if ($customermark!='')
	{
		// label
		$pdf->text($xmargin,$ymargin+25,$pdf_strings['YOUR_SIGN']);
		//content
		$pdf->text($xmargin+$xdistance,$ymargin+25,$customermark);
	}
}
// used to define the y location for the body
$ylocation_rightheader= $pdf->GetY();
// ************** End Top-Right Header *****************

// ************** Begin Top-Left Header **************
// Address
$xmargin = '20';
$ymargin = '55';
//senders info
$pdf->SetTextColor(120,120,120);
// companyBlockPositions -> x,y,width
$companyText=decode_html($org_name." - ".$org_address." - ".$org_code." ".$org_city);
$pdf->SetFont($default_font,'B',6);
$pdf->SetXY($xmargin, $ymargin);
$pdf->MultiCell(80,$pdf->getFontSize(), $companyText,0,'L',0);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont($default_font,'B',$font_size_address);
$billPositions = array($xmargin,$ymargin,"60");
if ($contact_name!='') 
{
	if ($bill_country!='Deutschland') {
		if ($contact_department!='') 
			$billText=$account_name."\n".$contact_department."\n".$contact_name."\n".$bill_street."\n".$bill_code." ".$bill_city."\n".$bill_country;
		else
			$billText=$account_name."\n".$contact_name."\n".$bill_street."\n".$bill_code." ".$bill_city."\n".$bill_country;
	}
	else {
		if ($contact_department!='') 
			$billText=$account_name."\n".$contact_department."\n".$contact_name."\n".$bill_street."\n".$bill_code." ".$bill_city;
		else
			$billText=$account_name."\n".$contact_name."\n".$bill_street."\n".$bill_code." ".$bill_city;
	}
}
elseif ($bill_country!='Deutschland') $billText=$account_name."\n".$bill_street."\n".$bill_code." ".$bill_city."\n".$bill_country;
else $billText=$account_name."\n".$bill_street."\n".$bill_code." ".$bill_city;

$pdf->SetFont($default_font, "", $font_size_address);
$pdf->SetXY ($xmargin,$ymargin);
3*$pdf->Ln(10);
$pdf->MultiCell(60,$pdf->getFontSize(), $billText,0,'L');
// ********** End Top-Left Header ******************
//***** empty space below the address required ************
$pdf->SetTextColor(255,255,255);
		//Line break
		$pdf->Ln(20);
//set start y location for body
if ($pdf->GetY() > $ylocation_rightheader) $ylocation_after = $pdf->GetY();
else $ylocation_after = $ylocation_rightheader;
$pdf->SetTextColor(0,0,0);

?>