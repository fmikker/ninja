<?php
# $Id: tc-lines1.php 1001 2011-08-08 02:22:55Z lbayuk $
# PHPlot test: Truecolor Lines plot with alpha and antialising
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'antialias' => True,      # If true, use anti-aliasing
  'alpha' => 60,           # Alpha adjustment for data colors, NULL to skip
  );
require 'tc-lines.php';
