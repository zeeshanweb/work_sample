<?php 
function gfdf_connect_db()
{
	global $gfdc_wpdb;
	$gfdc_wpdb = new wpdb(YDO_GFDC_USER,YDO_GFDC_PASS,YDO_GFDC_DB,YDO_GFDC_HOST);
}