CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `disposition`.`v_inb_line` AS
    SELECT 
        `disposition`.`inbound_line`.`No` AS `No`,
        `disposition`.`inbound_line`.`inbound` AS `inbound`,
        `disposition`.`inbound`.`position` AS `position`,
        `disposition`.`inbound_line`.`line` AS `inb_line`,
        `disposition`.`inbound_line`.`purchase_order` AS `purchase_order`,
        `disposition`.`inbound_line`.`po_line` AS `po_line`,
        `disposition`.`purchase_order_line`.`Line` AS `po_line_line`,
        `disposition`.`inbound`.`stock_location` AS `stock_location`,
        `disposition`.`purchase_order`.`vendor` AS `vendor`,
        `disposition`.`purchase_order`.`v_sales_order` AS `sales_order`,
        `disposition`.`inbound`.`v_delivery_note` AS `delivery_note`,
        `disposition`.`inbound`.`forwarder` AS `forwarder`,
        `disposition`.`inbound`.`truck` AS `truck`,
        `disposition`.`inbound`.`trailor` AS `trailor`,
        `disposition`.`inbound`.`container` AS `container`,
        `disposition`.`purchase_order`.`arrival` AS `po_arrival`,
        `disposition`.`inbound`.`arrival` AS `inb_arrival`,
        `disposition`.`purchase_order`.`transport_temp_min` AS `po_transport_temp_min`,
        `disposition`.`purchase_order`.`transport_temp_max` AS `po_transport_temp_max`,
        `disposition`.`inbound`.`transport_temperature` AS `inb_transport_temp`,
        `disposition`.`inbound_line`.`product` AS `product`,
        `disposition`.`product`.`product` AS `product_desc`,
        `disposition`.`inbound_line`.`variant` AS `variant`,
        `v_variant`.`variant` AS `variant_desc`,
        `v_variant`.`origin` AS `origin`,
        `v_variant`.`packaging_no` AS `packaging_no`,
        `v_variant`.`packaging` AS `packaging`,
        `v_variant`.`t_packaging` AS `t_packaging`,
        `v_variant`.`quality_no` AS `quality_no`,
        `v_variant`.`quality` AS `quality`,
        `v_variant`.`brand_no` AS `brand_no`,
        `v_variant`.`brand` AS `brand`,
        `v_variant`.`qc_no` AS `qc_no`,
        `v_variant`.`quality_class` AS `quality_class`,
        `v_variant`.`label_no` AS `label_no`,
        `v_variant`.`label` AS `label`,
        `v_variant`.`items` AS `items`,
        `v_variant`.`weight_item` AS `weight_item`,
        `disposition`.`purchase_order_line`.`Trading_Units` AS `po_tranding_units`,
        `disposition`.`purchase_order_line`.`PU_TU` AS `po_PU_TU`,
        `disposition`.`purchase_order_line`.`Packing_Units` AS `po_packing_units`,
        `disposition`.`purchase_order_line`.`pallet` AS `po_pallet`,
        `disposition`.`purchase_order_line`.`Pallets` AS `po_pallets`,
        `disposition`.`purchase_order_line`.`TU_Pallet` AS `po_tu_pallet`,
        `disposition`.`purchase_order_line`.`lot` AS `po_lot`,
        `disposition`.`purchase_order_line`.`barcode` AS `po_barcode`,
        `disposition`.`inbound_line`.`lot` AS `inb_lot`,
        `disposition`.`inbound_line`.`barcode` AS `inb_barcode`,
        `disposition`.`purchase_order_line`.`certificate` AS `po_certificate`,
        `disposition`.`inbound_line`.`certificate` AS `inb_certificate`,
        `disposition`.`purchase_order_line`.`brix_min` AS `po_brix_min`,
        `disposition`.`purchase_order_line`.`brix_max` AS `po_brix_max`,
        `disposition`.`inbound_line`.`brix` AS `inb_brix`,
        `disposition`.`inbound_line`.`trading_units` AS `inb_trading_units`,
        `disposition`.`inbound_line`.`packing_units` AS `inb_packing_units`,
        `disposition`.`inbound_line`.`pallet` AS `inb_pallet`,
        `disposition`.`inbound_line`.`pallets` AS `inb_pallets`,
        `disposition`.`inbound_line`.`tu_pallet` AS `inb_tu_pal`,
        `disposition`.`inbound_line`.`items_checked` AS `items_checked`,
        SUM((`disposition`.`movements`.`trading_units` * `disposition`.`movement`.`direction`)) AS `stock`,
        SUM(`v_qc_inb_line`.`grade_weighted`) AS `sum_grade_weighted`,
        (SUM(`v_qc_inb_line`.`grade_weighted`) / COUNT(`v_qc_inb_line`.`grade`)) AS `avg_grade_weighted`
    FROM
        ((((((((`disposition`.`inbound_line`
        JOIN `disposition`.`inbound` ON ((`disposition`.`inbound`.`No` = `disposition`.`inbound_line`.`inbound`)))
        JOIN `disposition`.`purchase_order` ON ((`disposition`.`purchase_order`.`No` = `disposition`.`inbound_line`.`purchase_order`)))
        JOIN `disposition`.`v_variant` ON ((`v_variant`.`No` = `disposition`.`inbound_line`.`variant`)))
        JOIN `disposition`.`purchase_order_line` ON ((`disposition`.`purchase_order_line`.`No` = `disposition`.`inbound_line`.`po_line`)))
        JOIN `disposition`.`product` ON ((`disposition`.`product`.`No` = `disposition`.`inbound_line`.`product`)))
        JOIN `disposition`.`movements` ON ((`disposition`.`movements`.`inbound_line` = `disposition`.`inbound_line`.`No`)))
        JOIN `disposition`.`movement` ON ((`disposition`.`movement`.`No` = `disposition`.`movements`.`movement`)))
        JOIN `disposition`.`v_qc_inb_line` ON ((`v_qc_inb_line`.`inbound_line` = `disposition`.`inbound_line`.`No`)))
    GROUP BY `disposition`.`inbound_line`.`No` , `disposition`.`inbound_line`.`inbound` , `disposition`.`inbound`.`position` , `disposition`.`inbound_line`.`line` , `disposition`.`inbound_line`.`purchase_order` , `disposition`.`inbound_line`.`po_line` , `disposition`.`purchase_order_line`.`Line` , `disposition`.`inbound`.`stock_location` , `disposition`.`purchase_order`.`vendor` , `disposition`.`inbound`.`forwarder` , `disposition`.`inbound`.`truck` , `disposition`.`inbound`.`trailor` , `disposition`.`inbound`.`container` , `disposition`.`purchase_order`.`arrival` , `disposition`.`inbound`.`arrival` , `disposition`.`purchase_order`.`transport_temp_min` , `disposition`.`purchase_order`.`transport_temp_max` , `disposition`.`inbound`.`transport_temperature` , `disposition`.`inbound_line`.`product` , `disposition`.`product`.`product` , `disposition`.`inbound_line`.`variant` , `v_variant`.`variant` , `v_variant`.`origin` , `v_variant`.`packaging_no` , `v_variant`.`packaging` , `v_variant`.`t_packaging` , `v_variant`.`quality_no` , `v_variant`.`quality` , `v_variant`.`brand_no` , `v_variant`.`brand` , `v_variant`.`qc_no` , `v_variant`.`quality_class` , `v_variant`.`label_no` , `v_variant`.`label` , `v_variant`.`items` , `v_variant`.`weight_item` , `disposition`.`purchase_order_line`.`Trading_Units` , `disposition`.`purchase_order_line`.`PU_TU` , `disposition`.`purchase_order_line`.`Packing_Units` , `disposition`.`purchase_order_line`.`pallet` , `disposition`.`purchase_order_line`.`Pallets` , `disposition`.`purchase_order_line`.`TU_Pallet` , `disposition`.`purchase_order_line`.`lot` , `disposition`.`purchase_order_line`.`barcode` , `disposition`.`inbound_line`.`lot` , `disposition`.`inbound_line`.`barcode` , `disposition`.`purchase_order_line`.`certificate` , `disposition`.`inbound_line`.`certificate` , `disposition`.`purchase_order_line`.`brix_min` , `disposition`.`purchase_order_line`.`brix_max` , `disposition`.`inbound_line`.`brix` , `disposition`.`inbound_line`.`trading_units` , `disposition`.`inbound_line`.`packing_units` , `disposition`.`inbound_line`.`pallet` , `disposition`.`inbound_line`.`pallets` , `disposition`.`inbound_line`.`tu_pallet`