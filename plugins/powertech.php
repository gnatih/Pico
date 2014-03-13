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
    
    foreach ($exclude['files'] as $s)
    { 
      $s = (substr($s, -1*strlen('index')) == 'index') ? substr($s, 0, -1*strlen('index')) : $s;
      $s = (substr($s, -1) == '/') ? $s : $s.'/';
      
      if ($url == $s)
      {
        return true;
      }
    }
     
    return false;
  }

  private function at_recursive($split = array(), $page = array(), $current_page = array())
  {
    $activeClass = (isset($this->settings['at_navigation']['activeClass'])) ? $this->settings['at_navigation']['activeClass'] : 'is-active';
    if (count($split) == 1)
    {     
      $is_index = ($split[0] == '') ? true : false;
      $ret = array(
        'title'     => $page['title'],
        'url'     => $page['url'],
        'class'     => ($page['url'] == $current_page['url']) ? $activeClass : ''
      );
      
      $split0 = ($split[0] == '') ? '_index' : $split[0];
      return array('_child' => array($split0 => $ret));
      return $is_index ? $ret : array('_child' => array($split[0] => $ret));
    }
    else
    {
      if ($split[1] == '')
      {
        array_pop($split);
        return $this->at_recursive($split, $page, $current_page);
      }
      
      $first = array_shift($split);
      return array('_child' => array($first => $this->at_recursive($split, $page, $current_page)));
    }
  }

  public function plugins_loaded()
  {
    
  }

  public function config_loaded(&$settings)
  {
    $this->settings = $settings;
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
    $twig_vars['navigation'] = $this->build_navigation($this->navigation, true); 
  }

  private function build_navigation($navigation = array(), $start = false)
  {
    $id = $start ? '' : '';
    $class = $start ? 'menu' : '';
    $class_li = 'leaf';
    $class_a = 'link';
    $child = '';
    $ul = $start ? '<ul id="%s" class="%s">%s</ul>' : '<ul>%s</ul>';
    
    if (isset($navigation['_child']))
    {
      $_child = $navigation['_child'];
      array_multisort($_child);
      
      foreach ($_child as $c)
      {
        $child .= $this->build_navigation($c);
      }
      
      $child = $start ? sprintf($ul, $id, $class, $child) : sprintf($ul, $child);
    }
    
    $li = isset($navigation['title'])
      ? sprintf(
        '<li class="%1$s %5$s"><a href="%2$s" class="%1$s %6$s" title="%3$s">%3$s</a>%4$s</li>',
        $navigation['class'],
        $navigation['url'],
        $navigation['title'],
        $child,
        $class_li,
        $class_a
      )
      : $child;
    
    return $li;
  }  
}

?>