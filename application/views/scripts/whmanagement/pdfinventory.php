<?php
function getTextWidth($text, $font, $font_size) {
    $drawing_text = iconv('', 'UTF-8', $text);
    $characters    = array();
    for ($i = 0; $i < strlen($drawing_text); $i++) {
        $characters[] = ord ($drawing_text[$i]);
    }
    $glyphs        = $font->glyphNumbersForCharacters($characters);
    $widths        = $font->widthsForGlyphs($glyphs);
    $text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
    return $text_width;
}

$palletlabel = new Zend_Pdf();
$page = new Zend_Pdf_Page(283, 425);
$palletlabel->pages[] = $page;
$fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
$fontNormal = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$textColor = new Zend_Pdf_Color_HTML('black');
$page->setFont($fontBold, 32);
$page->drawText(date('d.m.Y'), 10, 20, 'utf-8');
//$page->drawText('P '.$this->data['position'], 10, 45, 'utf-8');
//$page->drawText("{$this->data['items']}X{$this->data['weight_item']}g", 10, 80, 'utf-8');
echo $palletlabel->render();
