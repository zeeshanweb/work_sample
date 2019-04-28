jQuery(document).ready(function(){
});
function validatemanufacturerForm()
{
	var manufacturer_name = jQuery(".manufacturer_name").val();
	if ( manufacturer_name == "" )
	{
		alert("Company name must be filled out.");
        return false;
	}
}
function validateproductForm()
{
	var manufacturer_name = jQuery(".postform").val();
	if ( manufacturer_name == "" || manufacturer_name == '-1' )
	{
		alert("Please select manufacturer.");
        return false;
	}
	var mfg_name = jQuery(".mfg_name").val();
	if ( mfg_name == "" )
	{
		alert("Please select Mfg Name.");
        return false;
	}
}