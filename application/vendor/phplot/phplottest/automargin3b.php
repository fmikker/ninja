<?php
# $Id: automargin3b.php 1001 2011-08-08 02:22:55Z lbayuk $
# Testing phplot - Automatic margin calculation - 3b long ticks, not visible
# This is a parameterized test. See the script named at the bottom for details.
$tp = array(
  'suffix' => ', Long missing ticks',   # Title part 2
  'do_title' => True,           # True to display the main title
  'x_title_pos' => 'both',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'both',  # Y Title Position: plotleft plotright both none
  'xticklabel' => 'both', # X Tick & label position: none|both|plotup|plotdown
  'xtick' => 'none',        # X Tick override, if different from xticklabel
  'yticklabel' => 'both', # Y Tick & label position: none|both|plotleft|plotright
  'ytick' => 'none',        # Y Tick override, if different from yticklabel
  'xticklen' => 20,     # X Tick length (outside graph)
  'yticklen' => 25,     # Y Tick length (outside graph)
  );
require 'automargin.php';
