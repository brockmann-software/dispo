select `disposition`.`variant`.`No` AS `No`,
`disposition`.`variant`.`product` AS `product_no`,
`disposition`.`product`.`product` AS `product`,
concat(`disposition`.`country`.`Id`,' ',`disposition`.`product`.`product`,' ',`disposition`.`quality`.`quality`,' ',`disposition`.`variant`.`items`,'x',`disposition`.`variant`.`weight_item`,'g ',`disposition`.`packaging`.`packaging`,' ',`disposition`.`quality_class`.`class_short`,' ',`disposition`.`brand`.`brand`,' ',`disposition`.`label`.`label_short`) AS `variant`,
`disposition`.`variant`.`origin` AS `origin`,
`disposition`.`country`.`country` AS `origin_long`,
`disposition`.`variant`.`items` AS `items`,
`disposition`.`variant`.`weight_item` AS `weight_item`,
variant.packaging as packaging_no,
`disposition`.`packaging`.`packaging` AS `packaging`,
variant.label as label_no,
`disposition`.`label`.`label` AS `label`,
variant.quality_class as qc_no,
`disposition`.`quality_class`.`quality_class` AS `quality_class`,
`disposition`.`variant`.`t_packaging` AS `t_packaging`,
variant.brand as brand_no,
`disposition`.`brand`.`brand` AS `brand`,
variant.quality as quality_no,
`disposition`.`quality`.`quality` AS `quality`,
(`disposition`.`variant`.`items` * `disposition`.`variant`.`weight_item`) AS `weight_netto`,
(((`disposition`.`variant`.`items` * `disposition`.`variant`.`weight_item`) + (`disposition`.`variant`.`items` * `disposition`.`packaging`.`weight`)) + `disposition`.`tp_format`.`weight`) AS `weight_brutto`
from `disposition`.`variant`
join `disposition`.`product` on (`disposition`.`product`.`No` = `disposition`.`variant`.`product`)
join `disposition`.`country` on (`disposition`.`country`.`Id` = `disposition`.`variant`.`origin`)
join `disposition`.`packaging` on (`disposition`.`packaging`.`No` = `disposition`.`variant`.`packaging`)
join `disposition`.`label` on (`disposition`.`label`.`No` = `disposition`.`variant`.`label`)
join `disposition`.`brand` on (`disposition`.`brand`.`No` = `disposition`.`variant`.`brand`)
join `disposition`.`quality` on (`disposition`.`quality`.`No` = `disposition`.`variant`.`quality`)
join `disposition`.`tp_format` on (`disposition`.`tp_format`.`no` = `disposition`.`variant`.`t_packaging`)
join `disposition`.`quality_class` on (`disposition`.`quality_class`.`No` = `disposition`.`variant`.`quality_class`)