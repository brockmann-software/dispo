select `v_inb_line`.`No` AS `No`,
`v_inb_line`.`position` AS `position`,
`v_inb_line`.`inb_arrival` AS `arrival`,
`v_inb_line`.`variant_desc` AS `variant`,
`v_inb_line`.`origin` AS `origin`,
`v_inb_line`.`t_packaging` AS `t_packaging`,
`v_inb_line`.`packaging` AS `packaging`,
`v_inb_line`.`quality` AS `quality`,
`v_inb_line`.`brand` AS `brand`,
`v_inb_line`.`label` AS `label`,
`v_inb_line`.`inb_trading_units` AS `inb_trading_units`,
`v_inb_line`.`inb_pallets` AS `inb_pallets`,
v_inb_line.in
`disposition`.`inventory`.`date` AS `inventory_date`,
`disposition`.`inventory`.`tu_pallet` AS `tu_pallet`,
`disposition`.`inventory`.`inventory` AS `inventory_head`,
sum(`disposition`.`movements`.`trading_units`) AS `stock`,
sum(`disposition`.`inventory`.`pallets`) AS `sum_inv_pallets`,
sum(`disposition`.`inventory`.`trading_units`) AS `sum_inventory`
from ((`disposition`.`v_inb_line`
join `disposition`.`movements` on (`disposition`.`movements`.`type` = 0 and (`disposition`.`movements`.`shipment_line` = `v_inb_line`.`No`)
left join `disposition`.`inventory` on (`v_inb_line`.`No` = `disposition`.`inventory`.`inbound_line`)
group by `v_inb_line`.`No`,
`v_inb_line`.`position`,
`v_inb_line`.`inb_arrival`,
`v_inb_line`.`variant_desc`,
`v_inb_line`.`origin`,
`v_inb_line`.`t_packaging`,
`v_inb_line`.`packaging`,
`v_inb_line`.`quality`,
`v_inb_line`.`brand`,
`v_inb_line`.`label`,
`v_inb_line`.`inb_trading_units`,
`v_inb_line`.`inb_pallets`,
`disposition`.`inventory`.`date`,
`disposition`.`inventory`.`tu_pallet`,
`disposition`.`inventory`.`inventory`