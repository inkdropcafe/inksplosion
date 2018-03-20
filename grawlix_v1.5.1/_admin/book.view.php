<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
//$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$link = new GrlxLinkStyle;
$list = new GrlxList;
$sl = new GrlxSelectList;
$form = new GrlxForm;
$marker = new GrlxMarker;

$view-> yah = 3;

$var_list = array(
	'book_id','delete_page_id','start_sort_order','keyword','delete_marker_id','sel','add_marker_type','delete_all'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

if ( $start_sort_order && is_numeric($start_sort_order) )
{
	$_SESSION['start_sort_order'] = $start_sort_order;
}
elseif ( $_SESSION['start_sort_order'] && is_numeric($_SESSION['start_sort_order'] ) )
{
	$start_sort_order = $_SESSION['start_sort_order'];
}
else {
	$start_sort_order = 1;
}




/*****
 * Updates
 */

if ( $book_id ) {
	$book = new GrlxComicBook($book_id);
}
else {
	$book = new GrlxComicBook();
	$book_id = $book-> bookID;
}

$moving = false;

if ( $_GET['new_marker_on'] ) {
	$data = array (
		'title' => 'New marker',
		'marker_type_id' => 1
	);
	$new_marker_id = $db->insert('marker', $data);
	if ( $new_marker_id ) {
		$data = array (
			'marker_id' => $new_marker_id
		);
		$db->where('id',$_GET['new_marker_on']);
		$success = $db-> update('book_page', $data);
	}
	if ( $success ) {
		header('location:marker.edit.php?marker_id='.$new_marker_id);
		die();
	}
}

if ( $sel && $add_marker_type && $moving === false ) {

	$i = 1;
	foreach ( $sel as $key => $val ) {
		if ( count ( $sel ) > 1 ) {
			$new_marker_title = 'New '.$i;
			$i++;
		}
		else {
			$new_marker_title = 'New';
		}
		$data = array (
			'title' => $new_marker_title,
			'marker_type_id' => $add_marker_type
		);
		$new_marker_id = $db->insert('marker', $data);

		if ( $new_marker_id ) {
			$data = array (
				'marker_id' => $new_marker_id
			);
			$db->where('id',$val);
			$success = $db-> update('book_page', $data);
		}
		if ( $success && count ( $sel ) == 1 ) {
			header('location:marker.edit.php?marker_id='.$new_marker_id);
			die();
		}
	}
}

if ( $_POST && $_POST['sort_order'] ) {

	// Look for differences in orig (hidden fields) and sorts entered by the artist.
	foreach ( $_POST['sort_order'] as $moving_id => $maybe_new_order ) {

		// If you find a difference …
		if ( $maybe_new_order < $_POST['orig_sort_order'][$moving_id] ) {
			$maybe_new_order = $maybe_new_order - 0.999;
			$data = array ('sort_order' => $maybe_new_order);
			$db->where('id',$moving_id);
			$id = $db->update('book_page', $data);
			if ( $moving == false ) { $moving = $maybe_new_order; }
		}
		if ( $maybe_new_order > $_POST['orig_sort_order'][$moving_id] ) {
			$maybe_new_order = $maybe_new_order + 0.001;
			$data = array ('sort_order' => $maybe_new_order);
			$db->where('id',$moving_id);
			$id = $db->update('book_page', $data);
			$moving == false ? $moving = $moving_id : null;
			if ( $moving == false ) { $moving = $maybe_new_order; }
		}
	}

	if ( $moving && $sel ) {
		foreach ( $sel as $moving_id ) {
			$data = array ('sort_order' => $moving + $i);
			$db->where('id',$moving_id);
			$id = $db->update('book_page', $data);
			$i = 0.001;
		}
	}
	reset_page_order($book_id,$db);
}

if ( $delete_marker_id ) {
	$doomed_marker = new GrlxMarker($delete_marker_id);
	if ( $doomed_marker-> markerInfo ) {
		$doomed_marker-> deleteMarker($delete_marker_id,false);
	}
}
if ( $delete_page_id && is_numeric($delete_page_id) ) {
	$doomed_page = new GrlxComicPage($delete_page_id);
	if ( $doomed_page-> pageInfo ) {
		$doomed_page-> deletePage($delete_page_id,true);
	}
	reset_page_order($book_id,$db);
}

// DELETE EVERYTHING (disabled … for now)
if ( $delete_all && 1==2 ) {

	if ( $book_id ) {
		$book = new GrlxComicBook($book_id);
	}
	else {
		$book = new GrlxComicBook();
		$book_id = $book-> bookID;
	}

	if ( $book ) {
//		$book-> getPages();
	}
	if ( $book-> pageList )
	{
		foreach ( $book-> pageList as $key => $val )
		{
			$doomed_page = new GrlxComicPage($val['id']);
			if ( $doomed_page-> pageInfo ) {
//				$doomed_page-> deletePage($val['id'],true);
			}
		}
	}
}


/*****
 * Display logic
 */

///// Get the book info

if ( $book_id ) {
	$book = new GrlxComicBook($book_id);
}
else {
	$book = new GrlxComicBook();
	$book_id = $book-> bookID;
}

if ( $book ) {
	$book-> getPages();
	$book-> getMarkers();
	$total_pages = count($book-> pageList);
}

if ( $total_pages <= 100 ) {
	$pages_per_view = 10;
}
elseif ( $total_pages <= 200 ) {
	$pages_per_view = 20;
}
elseif ( $total_pages <= 500 ) {
	$pages_per_view = 50;
}
else {
	$pages_per_view = 70;
}

if ( $start_sort_order > $total_pages ) {
	$start_sort_order = $total_pages - $pages_per_view;
}



// For reference later
$marker_type_list = $db-> get ('marker_type',null,'id,title');

$marker_type_list = rekey_array($marker_type_list,'id');





///// Alerts and warnings

if ( !$book-> info ) {
	$alert_output .= $message->alert_dialog('This book ID '.$book_id.' doesn’t seem to exist.');
}

if ( !is_dir('../'.DIR_COMICS_IMG)) {
	@mkdir('../'.DIR_COMICS_IMG);
	$alert_output .= $message-> info_dialog('The '.DIR_COMICS_IMG.' folder was missing, so I created it. Just sayin’.');
}
elseif ( !is_writable('../'.DIR_COMICS_IMG) ) {
	$alert_output .= $message->alert_dialog('I can’t work with the '.DIR_COMICS_IMG.' folder. Looks like a permissions problem you need to fix via FTP.');
}

/*
$link-> title = 'Add a marker';
$link-> url = 'marker.create.php';
$link-> tap = 'Do something about that';

if ( !$book-> markerList && $book-> pageList ) {
	$alert_output .= $message->info_dialog('Hmm, no chapters here. '.$link-> paint().'.');
}
elseif ( !$book-> markerList && !$book-> pageList ) {
	$alert_output .= $message->info_dialog('Hmm, no chapters here. No pages either. <a href="marker.create.php">Do something about that</a>.');
}
*/

if ( !$book-> pageList) {
	$link-> title('Add a set of pages');
	$link-> url('book.pages-create.php');
	$link-> tap('Do something about that');

	$alert_output .= $message->info_dialog('Hmm, no pages here. '.$link->text_link().'.');
}





///// Display the list

if ( $book-> pageList ) {

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('book.page-edit.php');
	$edit_link->title('Edit page info.');
	$edit_link->reveal(false);
	$edit_link->action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link->url('book.view.php');
	$delete_link->title('Delete page.');
	$delete_link->reveal(false);
	$delete_link->action('edit');

	$marker_link = new GrlxLinkStyle;
	$marker_link->url('book.view.php');
	$marker_link->title('Remove this marker.');
	$marker_link->reveal(false);
	$marker_link->action('edit');
//	$marker_link->tap('Remove marker');


	$heading_list[] = array(
		'value' => 'Select',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Title',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Order',
		'class' => null
	);
	if ( $marker_type_list ) {
		$heading_list[] = array(
			'value' => 'Marker',
			'class' => null
		);
	}
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);

	$list-> headings($heading_list);
	$list-> draggable(false);
	$list-> row_class('chapter');

	$total_shown = 0; // Track how many images appear on the page.

	foreach ( $book-> pageList as $key => $val ) {

		$show_it = false;
		if (
			!$keyword
			&& (
				$total_pages < $pages_per_view
				|| (
					$val['sort_order'] >= $start_sort_order && $val['sort_order'] < $start_sort_order + $pages_per_view
				)
			)
		)
		{
			$show_it = true;
		}

		if ( $keyword ) {
			$findit = mb_strpos(mb_strtolower($val['title'],"UTF-8"), mb_strtolower($keyword,"UTF-8"));
			if ( $findit !== false ) {
				$show_it = true;
			}
		}

		if ( $show_it == true ) {

			// Set up options unique to this item in the list.
			$delete_link->query("delete_page_id=$val[id]");
			$edit_link->query("page_id=$val[id]");

			// General actions for this item.
			$action_output = $delete_link->icon_link('delete').$edit_link->icon_link();

			// Ensure we have something to display.
			$val['title'] ? $title = $val['title'] : $title = '<span class="fixme">Untitled</span>';

			// How big should the sort input fields be? 
			if ( $start_sort_order + $pages_per_view < 100 ) {
				$field_size = 4;
			}
			elseif ( $start_sort_order + $pages_per_view < 1000 ) {
				$field_size = 5;
			}
			elseif ( $start_sort_order + $pages_per_view < 10000 ) {
				$field_size = 6;
			}
			else {
				$field_size = 7;
			}

			// Build the sort_order field.
			$form-> input_number('sort_order['.$val['id'].']');
			$form-> value(intval($val['sort_order']));
			$form-> name('sort_order['.$val['id'].']');
			$form-> size($field_size);
			$order = $form-> paint();

			// Build the selection checkbox.
			$select = '<input type="checkbox" name="sel['.$val['id'].']" value="'.$val['id'].'"/>'."\n";

			// Keep track of the original order values so we can
			// compare against the artist’s entries. If they’re
			// different we know they want to rearrange comic pages.
			$form-> input_hidden('orig_sort_order['.$val['id'].']');
			$form-> value(intval($val['sort_order']));
			$form-> name('orig_sort_order['.$val['id'].']');
			$orig_output .= $form-> paint();

			// Got a marker? Oh boy.
			if ( $val['marker_id'] && $val['marker_id'] > 0 ) {

				// Add the marker-removal link.
				$marker_link->query("delete_marker_id=$val[marker_id]");

				$marker-> setID($val['marker_id']);
				$marker-> getMarkerInfo();

				// Emphasize top-tier markers with a <strong> element.
				if ( $marker-> markerInfo['marker_type_id'] == 1 ) {
					$marker_type = '<strong>'.$marker_type_list[1]['title'].'</strong>';
				}
				// Or not.
				else {
					$marker_type = $marker_type_list[$marker-> markerInfo['marker_type_id']]['title'];
				}

				$link-> url('marker.view.php?marker_id='.$val['marker_id']);
				$link-> title('Check out this marker');
				$link-> tap($marker-> markerInfo['title']);

				$this_marker = $marker_link->icon_link('remove') . $marker_type.': '.$link-> paint();
			}
			// No marker? Give them the ability to add one.
			elseif ( $marker_type_list ) {
				$link-> url('book.view.php?new_marker_on='.$val['id']);
				$link-> title('Mark a new section on page '.number_format($val['sort_order']).' that continues to the next marker.');
//				$link-> tap('<span style="color:#bbb">add marker</span>');
				$link-> icon_link('new');
				$this_marker = $link-> paint();
			}
			else {
				$this_marker = '';
			}
			// We’re done with the marker data for this item in the list. Whew.

			// Simple edit-this-page link.
			$link-> url('book.page-edit.php?page_id='.$val['id']);
			$link-> title('Edit page '.number_format($val['sort_order']).'.');
			$link-> tap($title);

			// Assemble the list item.
			$list_items[$val['id']] = array(
				'select'=> $select,
				'title'=> $link-> paint(),
				'sort_order'=> $order,
				'marker'=> $this_marker,
				'action'=> $action_output
			);

			// Track how many items appear on the page.
			// We need this to know whether or not the artist needs a “sort” option.
			$total_shown++;
		}
	}

	// Mix it all together.
	$list->content($list_items);
	$content_output  = $list->format_headings();
	$content_output .= $list->format_content();

}


/*
if ( $marker_type_list ) {

	$sl-> setName('add_marker_type');
	$sl-> setList($marker_type_list);
//	$sl-> setCurrent();
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:12rem');
	$select_options = $sl-> buildSelect().'<br/>'."\n";

}
*/


$link-> url('book.view.php');
$link-> title('Jump around the book.');

if ( $total_pages > $pages_per_view ) {
	for ( $i = 1; $i <= $total_pages; $i+=$pages_per_view) {
		$last = $i+$pages_per_view-1;
		$last > $total_pages ? $last = $total_pages : $last;
		if ( $start_sort_order >= $i && $start_sort_order <= $last ) {
			$link-> tap('<strong>'.$i.' &#8211; '.$last.'</strong>');
			$link-> query('start_sort_order='.$i);
			$pagination[] = $link-> paint()."\n";
		}
		else {
			$link-> tap($i.' &#8211; '.$last);
			$link-> query('start_sort_order='.$i);
			$pagination[] = $link-> paint();
		}
	}
	$pagination_output  = '<br/><p>Jump to '.implode(', ',$pagination).'</p>'."\n";
	$pagination_output .= '<input type="hidden" name="start_sort_order" value="'.$start_sort_order.'"/>';
}


/*****
 * Display
 */

$view->page_title('Book: '.$book-> info['title']);
$view->tooltype('book');
$view->headline('Book <span>'.$book-> info['title'].'</span>');

$link->url('book.edit.php?book_id='.$book_id);
$link->tap('Edit comic info');
$link->id('edit-comic-info');
$action_output = $link->text_link('editmeta');


/*
$link->url('marker.create.php');
$link->tap('Add pages');
$link->reveal(false);
$action_output .= $link->button_secondary('new');
*/
$view->action($action_output);


/*
$view->group_h2('New pages');
$view->group_instruction('INSTRUCTIONS GO HERE');
$view->group_contents($new_pages_output);
$content_output .= $view->format_group().'<hr/>';
*/



/*
$link->url('ajax.book-edit.php?book_id='.$book_id);
$link->tap('Edit comic meta');
$link->reveal(true);
$action_output = $link->text_link('editmeta');
$view->action($action_output);

if ( $_GET['tour'] ) {
	$joyride = <<<EOL
	<ol class="joyride-list" data-joyride data-options="tip_location:left;">
	  <li data-id="edit-comic-info" data-button="Next">
	    <p><strong>Edit comic info:</strong> Change your book’s title, publish frequency and marker types.</p>
	  </li>
	  <li data-id="th-4" data-button="Next" data-prev-text="Prev" data-options="tip_location:bottom;">
	    <p><strong>Markers:</strong> Add or remove chapters, scenes or other sections.</p>
	  </li>
	  <li data-id="th-3" data-button="Next" data-prev-text="Prev" data-options="tip_location:top;">
	    <p><strong>Order:</strong> Rearrange your pages by changing their numbers …</p>
	  </li>
	  <li data-id="sort-with-me" data-button="Done" data-prev-text="Prev" data-options="tip_location:bottom;">
	    <p>… then tap here to execute the move.</p>
	  </li>
	</ol>
EOL;
	$view->setJoyride($joyride);
}
*/

// Group

if ( $total_shown > 1 ) {

	// Group
	$view->group_h2('Reorder pages');
	$view->group_instruction('Change the pages’ numbers above to move them around the book.');
	$view->group_contents('<button class="btn primary save" name="submit" id="sort-with-me" type="submit" value="reorder"><i></i>Sort</button>'."\n");
	$reorder_output .= $view->format_group();
}

/*
$link-> title('Learn more about markers');
$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/markers');
$link-> tap('Markers');

$view->group_h2('Add marker');
$view->group_instruction($link-> external_link().' are sections of a book, like chapters, scenes or supplemental material. Use the checkboxes above to choose page(s) to begin new sections.');
$view->group_contents(
	$select_options .
 '<button class="btn primary new" name="submit" type="submit" value="add"><i></i>Add</button>'
);
$select_output .= $view->format_group();
*/

$search_form = <<<EOL
<div class="row">
	<div class="large-12 columns">
		<div class="row">
			<div class="small-3 columns">
				<input type="search" name="keyword" id="keyword" placeholder="Search for" value="$keyword"/>
			</div>
			<div class="small-9 columns">
				<button class="btn secondary search" name="submit" type="submit" value="reorder"><i></i>Search</button>
			</div>
		</div>
		<!--div class="row">
			<div class="small-12 columns">
				<a href="?delete_all=1" class="warning">DELETE ALL</a> — there is no undo!
			</div>
		</div-->
	</div>
</div>

EOL;


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= '<form accept-charset="UTF-8" method="post" action="book.view.php">'."\n";
$output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$output .= $search_form;
$output .= $content_output;
$output .= $orig_output;
$output .= $pagination_output;
//$output .= '<hr />'.$select_output;
if ( $total_shown > 1 ) {
	$output .= '<br/>'.$reorder_output;
}

$output .= '</form>'."\n";

print($output);




/*
$js_call = <<<EOL
	$( "i.sort" ).hover( // highlight a draggable row
		function() {
			$( this ).parent().parent().addClass("dragging");
		}, function() {
			$( this ).parent().parent().removeClass("dragging");
		}
	);
	$( "a.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
	$( "i.delete" ).hover( // highlight a row to be deleted
		function() {
			$( this ).parent().parent().addClass("red-alert");
		}, function() {
			$( this ).parent().parent().removeClass("red-alert");
		}
	);
	$( '[id^="id-"]' ).click( // delete item
		function() { // update the db
			var item = $(this).attr('id'); // id of the item to delete
			var container = $('#'+item).parent().parent();
			$.ajax({
				url: "ajax.book-delete.php",
				data: "delete-chapter=" + item,
				dataType: "html",
				success: function(data){
					$(container).remove();
					renumberOrder( '[id^="sort-"]', 1 );
				}
			});
		}
	);
	$( "#sortable" ).sortable({ // sort items
		activate: function(event, ui) { // highlight the dragged item
			$( ui.item ).children().addClass("dragging");
		},
		deactivate: function(event, ui) { // turn off the highlight
			$( ui.item ).children().removeClass("dragging");
			renumberOrder( '[id^="sort-"]', 1 );
		},
		update: function() {
			serial = $('#sortable').sortable('serialize');
			$.ajax({
				url: "ajax.sort.php",
				type: "post",
				data: serial,
				success: function(data){
					var obj = jQuery.parseJSON(data);
				},
				error: function(){
					alert("AJAX error");
				}
			});
		}
	});
	$( "#sortable" ).disableSelection();
EOL;
*/


//$view->add_jquery_ui();
//$view->add_inline_script($js_call);
print( $view->close_view() );
