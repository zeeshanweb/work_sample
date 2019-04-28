<?php 
if ( !class_exists('YdoMigrationDisplayError') )
{
	class YdoMigrationDisplayError
	{
		public function __construct()
		{
		}
		public static function begin()
		{
			ob_implicit_flush(true);
			header('Cache-Control: no-cache'); 
		}
		public static function print_message( $message, $type = '', $bold = false )
		{
			//return false;
			echo "<br>";
			if( 'success' == $type )
			{
				echo '<span  style="color:#008000;">';
			}
			else if( 'failed' == $type )
			{
				echo '<span  style="color:#FF0000;">';
			}
			else if( 'warning' == $type )
			{
				echo '<span  style="color:#fff3cd;">';
			}
			else 
			{
				echo '<span>';
			}
			if( true == $bold )
			{
				echo '<b>';
			}
			echo $message;
			if( true == $bold )
			{
				echo '</b>';
			}
			echo '</span>';
			echo PHP_EOL;
      		ob_flush();
			flush();
		}
		
	}
}