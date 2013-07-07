<?php

/*
 * This file is part of the {project_name}.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dayax\wordpress;

use Symfony\Component\BrowserKit\Client as BaseClient;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request;

/**
 * Client simulates a browser and makes requests to wordpress
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class Client extends BaseClient
{
    private $current_headers = array();
    
    public function __construct(array $server = array(), \Symfony\Component\BrowserKit\History $history = null, \Symfony\Component\BrowserKit\CookieJar $cookieJar = null)
    {
        parent::__construct($server, $history, $cookieJar);
        
        add_filter('wp_headers',array($this,'onWpSendHeaders'),999);                
    }
    
    public function doRequest($request)
    {                
        /*exec('php '.__DIR__.'/runner.php',$content);        
        $content = implode("\n",$content);
        return new Response($content);*/                    
        
        $vars = array(
        'SERVER_PROTOCOL'=>'HTTP/1.1',
        'SCRIPT_NAME'=>'/index.php',
        'PHP_SELF'=>'/index.php',
        'HTTP_ACCEPT'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'HTTP_USER_AGENT'=>'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36',
        'HTTP_CONNECTION'=>'keep-alive',
        'HTTP_CACHE_CONTROL'=>'max-age=0',
        'HTTP_ACCEPT_ENCODING'=>'gzip,deflate,sdch',
        'HTTP_ACCEPT_LANGUAGE'=>'en-US,en;q=0.8',
        );
        
        foreach($vars as $name=>$v){
            $_SERVER[$name]=$v;
        }
        
        $_GET = $_POST = array();
        foreach (array('query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow') as $v) {
            if (isset($GLOBALS[$v]))
                unset($GLOBALS[$v]);
        }
        
        $parts = parse_url($request->getURI());
        
        if (isset($parts['scheme'])) {
            $req = $parts['path'];
            if (isset($parts['query'])) {
                $req .= '?'.$parts['query'];
                // parse the url query vars into $_GET
                parse_str($parts['query'], $_GET);
            }
        } else {            
            $req = $url;
        }
        if (!isset($parts['query'])) {
            $parts['query'] = '';
        }

        $_SERVER['REQUEST_URI'] = $req;
        unset($_SERVER['PATH_INFO']);

        $this->flushCache();
        unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
        $GLOBALS['wp_the_query'] = & new \WP_Query();
        $GLOBALS['wp_query'] = & $GLOBALS['wp_the_query'];
        $GLOBALS['wp'] = & new \WP();

        // clean out globals to stop them polluting wp and wp_query
        foreach ($GLOBALS['wp']->public_query_vars as $v) {
            unset($GLOBALS[$v]);
        }
        foreach ($GLOBALS['wp']->private_query_vars as $v) {
            unset($GLOBALS[$v]);
        }

        $GLOBALS['wp']->main($parts['query']);
        
        $content = $this->getContent();
        
        return new Response($content,$this->getWpResponseStatus(),$this->current_headers);
    }
    
    private function getWpResponseStatus()
    {
        if(is_404()){
            return 404;
        }else{
            return 200;
        }
    }
    
    
    private function flushCache()
    {
        global $wp_object_cache;
        $wp_object_cache->group_ops = array();
        $wp_object_cache->stats = array();
        $wp_object_cache->memcache_debug = array();
        $wp_object_cache->cache = array();
        if (method_exists($wp_object_cache, '__remoteset')) {
            $wp_object_cache->__remoteset();
        }
        wp_cache_flush();
    }
    
    public function onWpSendHeaders($headers)
    {
        if(!is_array($headers)){
            $headers = array();
        }
        $this->current_headers = $headers;
    }
    
    private function getContent()
    {
        $template = false;
        if     ( is_404()            && $template = get_404_template()            ) :
        elseif ( is_search()         && $template = get_search_template()         ) :
        elseif ( is_tax()            && $template = get_taxonomy_template()       ) :
        elseif ( is_front_page()     && $template = get_front_page_template()     ) :
        elseif ( is_home()           && $template = get_home_template()           ) :
        elseif ( is_attachment()     && $template = get_attachment_template()     ) :
            remove_filter('the_content', 'prepend_attachment');
        elseif ( is_single()         && $template = get_single_template()         ) :
        elseif ( is_page()           && $template = get_page_template()           ) :
        elseif ( is_category()       && $template = get_category_template()       ) :
        elseif ( is_tag()            && $template = get_tag_template()            ) :
        elseif ( is_author()         && $template = get_author_template()         ) :
        elseif ( is_date()           && $template = get_date_template()           ) :
        elseif ( is_archive()        && $template = get_archive_template()        ) :
        elseif ( is_comments_popup() && $template = get_comments_popup_template() ) :
        elseif ( is_paged()          && $template = get_paged_template()          ) :
        else :
            $template = get_index_template();
        endif;
        
        if ( $template = apply_filters( 'template_include', $template ) ){
            ob_start();            
            include( $template );
            $contents = ob_get_contents();
            ob_end_clean();
            
            return $contents;
        }else{            
            return;
        }
    }
}
