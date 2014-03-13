<?php

/**
 * Example hooks for a Pico plugin
 *
 * @author Gilbert Pellegrom
 * @link http://pico.dev7studios.com
 * @license http://opensource.org/licenses/MIT
 */
class Powertech {
  private $settings = array();
  private $navigation = array();
  
  private function at_exclude($page)
  {
    $exclude = $this->settings['exclude'];
    $url = substr($page['url'], strlen($this->settings['base_url'])+1);
    $url = (substr($url, -1) == '/') ? $url : $url.'/';
    
    foreach ($exclude['single'] as $s)
    { 
      $s = (substr($s, -1*strlen('index')) == 'index') ? substr($s, 0, -1*strlen('index')) : $s;
      $s = (substr($s, -1) == '/') ? $s : $s.'/';
      
      if ($url == $s)
      {
        return true;
      }
    }
    
    foreach ($exclude['folder'] as $f)
    {
      $f = (substr($f, -1) == '/') ? $f : $f.'/';
      $is_index = ($f == '' || $f == '/') ? true : false;
      
      if (substr($url, 0, strlen($f)) == $f || $is_index)
      {
        return true;
      }
    }
    
    return false;
  }

  public function plugins_loaded()
  {
    
  }

  public function config_loaded(&$settings)
  {
    
  }
  
  public function request_url(&$url)
  {
    
  }
  
  public function before_load_content(&$file)
  {
    
  }
  
  public function after_load_content(&$file, &$content)
  {
    
  }
  
  public function before_404_load_content(&$file)
  {
    
  }
  
  public function after_404_load_content(&$file, &$content)
  {
    
  }
  
  public function before_read_file_meta(&$headers)
  {
    
  }
  
  public function file_meta(&$meta)
  {
    
  }

  public function before_parse_content(&$content)
  {
    
  }
  
  public function after_parse_content(&$content)
  {
    
  }
  
  public function get_page_data(&$data, $page_meta)
  {
    
  }
  
  public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
  {
    $navigation = array();
    
    foreach ($pages as $page)
    {
      if (!$this->at_exclude($page))
      {
        $_split = explode('/', substr($page['url'], strlen($this->settings['base_url'])+1));
        $navigation = array_merge_recursive($navigation, $this->at_recursive($_split, $page, $current_page));
      }
    }
    
    array_multisort($navigation);
    $this->navigation = $navigation;

  }
  
  public function before_twig_register()
  {
    
  }
  
  public function before_render(&$twig_vars, &$twig, &$template)
  {
    
  }
  
  public function after_render(&$output)
  {
    
  }
  
}

?>