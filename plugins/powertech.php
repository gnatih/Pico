<?php

/**
 * Pico Plugin for Powertech
 *
 * @author Jayesh Gohel
 * @link http://gnat.pencilheadz.net
 * @license http://opensource.org/licenses/MIT
 */
class Powertech {


	private $path;
	private $root;
	private $pages;
	private $pages_urls;
	private $current_url;
	private $base_url;
	private $hide_list;
	private $sub_menu;

	public function request_url(&$url)
	{
		$this->path = $this->format($url);
	}

	public function before_read_file_meta(&$headers) {
		$headers['placing'] = 'Placing';
	}

	public function get_page_data(&$data, $page_meta) {
		$data['placing'] = isset($page_meta['placing']) ? intval($page_meta['placing']) : 0;
	}

	public function config_loaded(&$settings)
	{
		$this->root = (!empty($settings["images_path"])) ? $this->format($settings["images_path"]) : 'content/';
		$this->base_url = $settings['base_url'];
		$this->hide_list = array_map('trim', explode(',', $settings['hide_pages']));
		$this->placing = ($settings["pages_order_by"] == "placing") ? true : false;
	}

	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['images'] = $this->images_list($twig_vars['base_url']);
		foreach($this->pages["_childs"] as $page){
			
			if(isset($page["_childs"]) && ($this->current_url == $page["url"] || strpos($this->current_url, $page['url']) === 0)){
				$twig_vars["secondary_menu"] = $this->output($page);
				$twig_vars["parent"] = $page;
				$this->path = $this->format($page['url']);
				$twig_vars["images"] = $this->images_list($twig_vars['base_url']);
			}
		}
		$twig_vars['primary_menu'] = $this->output($this->pages);

		$twig_vars['submenu'] = '<a href="'.$this->base_url.'">Home</a>';

		foreach($this->sub_menu as $page){
			$class = $this->current_url == $page['url'] ? 'active' : "";

			$twig_vars['submenu'].= '<a href="'.$page['url'].'" class="'.$class.'">'.$page['title'].'</a>';
		}
	}

	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		$this->pages_urls = array();
		foreach ($pages as $p) {
			$this->pages_urls[] = $p['url'];
		}

		$this->pages = array();
		$this->current_url = $current_page['url'];


		if($this->placing){
			$sorted_pages = array();

			$placing_id = 0;
			foreach ($pages as $page) {
				$sorted_pages[$page['placing'] . $placing_id] = $page;
				$placing_id++;
			}

			ksort($sorted_pages);
			$pages = $sorted_pages;
		}

		$this->construct_pages($pages);
	}

	private function format($path)
	{
		if( !$path ) return;

		$path = str_replace($this->base_url, "", $path);
		$is_index = strripos($path, 'index') === strlen($path)-5;
		if( $is_index ) return substr($path, 0, -5);
		elseif( substr($path, -1) != '/' ) $path .= '/';

		return $path;
	}

	private function images_list($base_url)
	{
		$images_path = $this->root . $this->path;

		$data = array();
		$pattern = '*.{[jJ][pP][gG],[jJ][pP][eE][gG],[pP][nN][gG],[gG][iI][fF]}';
		$images = glob(ROOT_DIR .'/'. $images_path . $pattern, GLOB_BRACE);

		foreach( $images as $path )
		{
			list(, $basename, $ext, $filename) = array_values(pathinfo($path));

			$img_info = getimagesize($path);
			$width = $img_info[0];
			$height = $img_info[1];
			$size = $img_info[2];
			$mime = $img_info['mime'];

			$data[] = array (
				'url' => $base_url .'/'. $images_path . $basename,
				'path' => $images_path,
				'name' => $filename,
				'ext' => $ext,
				'width' => $width,
				'height' => $height,
				'size' => $size
			);
		}
		return $data;
	}

	private function construct_pages($pages)
	{
		array_multisort($this->pages_urls, SORT_ASC, $pages);
		foreach ($pages as $page)
		{
			$page['path'] = rtrim(str_replace($this->base_url.'/','',$page['url']), '/');
			if($this->is_hidden($page['path'])) {
				$this->sub_menu[] = $page;
				continue;
			}

			$nested_path = $this->nested_path($page);
			$this->pages = array_merge_recursive($this->pages, $nested_path);
		}
	}

	private function nested_path($page)
	{
		$parts = explode('/', $page['path']);
		$count = count($parts);

		$arr = array();
		$parent = &$arr;
		foreach($parts as $id => $part) {
			$value = array();
			if(!$part || $id == $count-1) {
				$value = $page;
			}
			if(!$part) {
				$parent = $value;
				break;
			}
			$parent['_childs'][$part] = $value;
			$parent = &$parent['_childs'][$part];
		}
		return $arr;
	}

	private function output($pages)
	{
		if(!isset($pages['_childs'])) return '';

		$html = '<ul>';
		foreach ($pages['_childs'] as $page)
		{
			if($this->is_hidden($page['path'])) continue;

			$url = $page['url'];
			$filename = basename($url);
			$childs = $this->output($page);

			// use title if the page have one, and make a link if the page exists.
			$item = !empty($page['title']) ? $page['title'] : $filename;
			$class = !empty($page['color']) ? $page['color'] : '';
			if(in_array($url, $this->pages_urls))
				$item = '<a href="'.$url.'" class="'.$class.'">'.$item.'</a>';

			// add the filename in class, and indicates if is current or parent
			$class = $filename;
			if($this->current_url == $url) {
				$class .= ' is-current';

			} elseif(strpos($this->current_url, $url) === 0) {
				$class .= ' is-parent';
			}

			$html .= '<li class="'.$class.'">' . $item . $childs . '</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	private function is_hidden($path)
	{
		foreach($this->hide_list as $p)
		{
			if( !$p ) continue;
			if( $path == $p ) return true;
			if( strpos($path, $p) === 0 ) {
				if( substr($p,-1) == '/' ) return true;
				elseif( $path[strlen($p)] == '/' ) return true;
			}
		}
		return false;
	}
	
}

?>