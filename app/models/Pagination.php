<?php
namespace app\models;
/**
 * 
 */
class Pagination
{
	
	function __construct()
	{
		# code...
	}

	private $limit;
    private $page;
    private $query;
    private $total;


    public static function createLinks($pn, $totalPages, $filter_data = [])
    {
		$output = "";

    	$filter_data = htmlentities(json_encode($filter_data));

    	if ($pn > 1) {
	    		$output .= '<a href="javascript:void(0);" class="previous" id="prev-page"
	    		data-page="'.($pn-1).'" data-filter="'.$filter_data.'" title="Previous Page"><span>&#10094; Previous</span></a>';
	    	}

			if (($pn - 1) > 1) {
	    		$output .= '<a href="javascript:void(0);" data-page="1" data-filter="'.$filter_data.'"><div class="page-a-link">1</div></a>
	                <div class="page-before-after">...</div>';
			}

			for ($i = ($pn - 1); $i <= ($pn + 1); $i ++) {
	    		if ($i < 1)
	        		continue;
	    		if ($i > $totalPages)
	        		break;
	    		if ($i == $pn) {
	        		$class = "page-active";
	    		} else {
	        		$class = "page-a-link";
	    		}

	    		$output .= '<a href="javascript:void(0);" data-page="'.$i.'" data-filter="'.$filter_data.'">
	              	<div class="'.$class.'">'. $i . '</div></a>';
			}

			if (($totalPages - ($pn + 1)) >= 1) {
	    		$output .=  '<div class="page-before-after">...</div>';

			}

			if (($totalPages - ($pn + 1)) > 0) {
	    		if ($pn == $totalPages) {
	        		$class = "page-active";
	    		} else {
	        		$class = "page-a-link";
	    		}

	    		$output .=  '<a href="javascript:void(0);" data-page="'.$totalPages.'" data-filter="'.$filter_data.'">
	    			<div class="' . $class . '">' . $totalPages . '</div></a>';
			}


	    	if ($pn < $totalPages) {
	        	$output .= '<a href="javascript:void(0);" class="next" id="next-page"
	                    data-page="'.($pn+1).'" data-filter="'.$filter_data.'" title="Next Page">
	                    <span>Next &#10095;</span></a>';
	    	}

	    	return $output;
    }
}