<?php
define("ZF_PATH", realpath("/www-dev/ZendFramework-1.12.20/library"));
set_include_path(get_include_path() . PATH_SEPARATOR . ZF_PATH);
require_once "Zend/Loader/Autoloader.php";

$loader = Zend_Loader_Autoloader::getInstance();

$invoice = Zend_Pdf::load("template.pdf");
$page = $invoice->pages[0];

$customerName = "Angelina Jolie";
$invoiceId = "DF-00025786423";

// items in the array are product description,
// unit price, quantity purchased, and total price
// items in the array are product description,
// quantity purchased, unit price, and total price
$items = array(array("Golden Globe Polish", 1, 25.50, 25.50),
               array("Trophy Shelf", 2, 180.00, 360.00),
               array("DIY Tattoo Kit", 1, 149.99, 149.99));
$subtotal = 535.49;
$discount = 10;
$amountDue = 481.94;

// specify font
$fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
$page->setFont($fontBold, 12);

// specify color
$color = new Zend_Pdf_Color_HTML("navy");
$page->setFillColor($color);

$page->drawText($customerName, 110,641);
// another font
$fontNormal = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$page->setFont($fontNormal, 12);

// invoice information
$page->drawText($invoiceId, 420,642);
$page->drawText(date("M d, Y"), 420,628);
$page->drawText('$' . number_format($subtotal, 2), 510,143);
$page->drawText($discount . "%", 510,123);
$page->drawText('$' . number_format($amountDue, 2), 510,103);

// purchase items
$posY = 560;
foreach ($items as $item) {
    $page->drawText($item[0], 50, $posY);
    $page->drawText($item[1], 350, $posY);
    $page->drawText(number_format($item[2], 2), 430, $posY);
    $page->drawText(number_format($item[3], 2), 510, $posY);
    $posY -= 22.7;
}

// instruct browser to download the PDF
header("Content-Type: application/x-pdf");
header("Content-Disposition: attachment; filename=invoice-". date("Y-m-d-H-i") . ".pdf");
header("Cache-Control: no-cache, must-revalidate");

// output the PDF
echo $invoice->render();

