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
	$pdf->SetXY('130','15');
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
//quote number
// quote number-label
$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['NUM_FACTURE_NAME'],0,0);
//quote number-content
$pdf->SetX($xmargin+$xdistance);
$pdf->Cell(20,$pdf->getFontSize(),$quote_no,0,1);
//quote date
//quote date - label
$pdf->SetX($xmargin);
$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['QUOTEDATE'],0,0);
//quote date -content
$pdf->SetX($xmargin+$xdistance);
$pdf->Cell(20,$pdf->getFontSize(),$date_to_display,0,1);
//due date number
//due date - label
$pdf->SetX($xmargin);
$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['VALID_TILL'],0,0);
//due date -content
$pdf->SetX($xmargin+$xdistance);
$pdf->Cell(20,$pdf->getFontSize(),$valid_till,0,1);

//print owner if requested
if ($owner =='true'){
	//owner label
	$pdf->SetX($xmargin);
	$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['ISSUER'],0,0);
	//owner-content
	$pdf->SetX($xmargin+$xdistance);
	$pdf->Cell(20,$pdf->getFontSize(),decode_html($owner_firstname).' '.decode_html($owner_lastname),0,1);
}
if ($ownerphone =='true'){
	//owner label
	$pdf->SetX($xmargin);
	$pdf->Cell(20,$pdf->getFontSize(),$pdf_strings['PHONE'],0,0);
	//owner-content
	$pdf->SetX($xmargin+$xdistance);
	$pdf->Cell(20,$pdf->getFontSize(),$owner_phone,0,1);
}
//print requisition# if set
if ($requisition_no!='')
{
	$pdf->SetXY($xmargin+25, $ymargin+35 );
	$pdf->Cell(20,$pdf->getFontSize(), $pdf_strings['NUM_FACTURE_NAME']." ",0,0,'L');
	$pdf->SetXY($xmargin+50, $ymargin+35 );
	$pdf->Cell(20,$pdf->getFontSize(), decode_html($requisition_no),0,0,'R');
}
//print customer markif set
if ($customermark!='')
{
	$pdf->SetXY($xmargin+25, $ymargin+39 );
	$pdf->Cell(20,$pdf->getFontSize(), $pdf_strings['VAR_CUSTOMERMARK']." ",0,0,'L');
	$pdf->SetXY($xmargin+50, $ymargin+39 );
	$pdf->Cell(20,$pdf->getFontSize(), decode_html($customermark),0,0,'R');
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
$companyText=decode_html ($org_name." - ".$org_address." - ".$org_code." ".$org_city);
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
$billText = decode_html ($billText);
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