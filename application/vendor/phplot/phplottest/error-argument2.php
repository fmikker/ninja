<?php
# $Id: error-argument2.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot error test - argument error with handler
require 'esupport.php';
set_error_handler('test_catch_exit');
require 'error-argument.php';
