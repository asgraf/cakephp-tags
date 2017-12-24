<?php
	if (!isset($tags)) {
		$tags = array();
	}
	if (!isset($url)) {
		$url = array();
	}
?>

<div class="tagList">
<ul>
<?php
	foreach ($tags as $tag) {
		$tagUrl = $url;
		if (!empty($tag['keyname'])) {
			$tagUrl['by'] = $tag['keyname'];
			$name = $this->Html->link($tag['name'], $tagUrl);
		} else {
			$name = h($tag['name']);
		}
		echo '<li>'.$name.'</li>';
	}
?>
</ul>
</div>