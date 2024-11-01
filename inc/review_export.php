<?php
defined( 'ABSPATH' ) or exit;
class SWREI_reviewexport {
	
	public function __construct() {
		
	}
	public static function SWREI_simple_reviewexporter(){	
	
		$sdata = json_decode(SWREI_reviewexport::SWREI_getCommentReview());
		
		if(is_array($sdata) && !empty($sdata)){		
			
			/* Comment data*/
			$sdata  = SWREI_reviewexport::SWREI_objectToArray($sdata);
			
			/*header keys*/
			$allKeys = SWREI_reviewexport::SWREI_getAllArraykeys($sdata);
			
			/* Export data in CSV file */
			SWREI_reviewexport::SWREI_exportreview_csv($allKeys,$sdata);
			
			return true;
			
		}else{ 
			return false;
		}		

	}
	
	public static function SWREI_getCommentReview(){
		
		$args = array ('post_type' => 'product');
		$comments = get_comments( $args );	

		$return = array();	
		
		foreach($comments as $comment){
			
			$comment_data = array();						
			$item =array();	
			
			/*comment data*/
			foreach($comment as $key=>$e){
				$item['data:'.$key] = $e;
			}	
	
			/*comment meta data*/
			$comment_meta = get_comment_meta( $comment->comment_ID );						
			foreach($comment_meta as $k=>$m){
				
				$item['meta:'.$k] = $m[0];
			}
			
			$return[] = $item;
			
			
		}
		
		return json_encode($return);
		
	}

	public static function SWREI_exportreview_csv($allKeys,$sdata){
		
		ob_clean();		
		$filename = date('dMY_Hi').'-exportreviws.csv';
		header("Pragma: public");
		header("Expires: 0");
		header("Content-Type: text/csv;");		
		header("Content-Disposition: Attachment; Filename=\"$filename\"");
		header("Content-Transfer-Encoding: UTF-8");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");	
		header("Content-Transfer-Encoding: binary");		
		 
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array_keys($allKeys));
		foreach($sdata as $values){
			fputcsv($fp, $values);			 
		}
		fclose($fp);
		 
		ob_flush();			
		exit;
		
	}
	
	public function SWREI_objectToArray($array){
		
		$return = array();
		foreach($array as $arr){
			
			$outSingle = array();
			
			foreach($arr as $key=>$singleItem){
				$outSingle[$key] = $singleItem;
			}
			
			$return[] = $outSingle;
		}
		
		return $return;
	}
	
	public function SWREI_getAllArraykeys($array){
		
		$return = array();		
		foreach($array as $arr){		
			
			foreach($arr as $key=>$singleItem){
				if(!array_key_exists($key,$return)){
					$return[$key] = 'NA';
				}
			}
		}
		return $return;
	}
	
	
	
}
 ?>