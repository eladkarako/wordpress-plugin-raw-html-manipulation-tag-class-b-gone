<?php
  /**
   * Plugin Name:        Tag-Class-B-Gone
   * Donate link:        https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=7994YX29444PA
   * License:            GPL2
   * Version:            4.1.0
   * Description:        Ruthless HTML-Manipulation At A Level Beyond WordPress-API, Slimming Your Page And Client-Loading Times. WordPress Adds A Class For Each TAG You Add To Your Post, This WordPress-Plugin Puts-An-End To That.
   * Author:             eladkarako
   * Author Email:       The_Author_Value_Above@gmail.com
   * Author URI:         http://icompile.eladkarako.com
   * Plugin URI:         https://github.com/eladkarako/wordpress-plugin-raw-html-manipulation-minifier
   */


/* ╔═════════════════════════════════════════════════════╗
   ║ - Hope You've Enjoyed My Work :]                    ║
   ╟─────────────────────────────────────────────────────╢
   ║ - Feel Free To Modifiy And Distribute it (GPL2).    ║
   ╟─────────────────────────────────────────────────────╢
   ║ - Donations Are A *Nice* Way Of Saying Thank-You :] ║
   ║   But Are NOT Necessary!                            ║
   ║                                                     ║
   ║ I'm Doing It For The Fun Of It :]                   ║
   ║                                                     ║
   ║    - Elad Karako                                    ║
   ║         Tel-Aviv, Israel- August 2016.              ║
   ╚═════════════════════════════════════════════════════╝
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ */


call_user_func(function () {
  /* ---------------------------------------------------------------------------------------------- */
  $is_engine_ok = call_user_func(function(){
                    function is_server_val($attribute, $regex){
                      return (1 === preg_match($regex, filter_input(INPUT_SERVER,$attribute)));
                    }

                    $is_php     = is_server_val('PHP_SELF',   '#index\.php$#i'   );  /* WordPress-Template-engine */
                    $is_admin   = is_server_val('SCRIPT_URL', '#\/wp\-admin\/#i' );  /* admin folder */
                    $is_feed    = is_server_val('SCRIPT_URL', '#\/feed\/#i'      ) || is_server_val('REDIRECT_SCRIPT_URL',  '#\/feed\/#i'      );
                    $is_atom    = is_server_val('SCRIPT_URL', '#\/atom\/#i'      ) || is_server_val('REDIRECT_SCRIPT_URL',  '#\/atom\/#i'      );
                    $is_json    = is_server_val('SCRIPT_URL', '#\/wp\-json\/#i'  ) || is_server_val('REDIRECT_SCRIPT_URL',  '#\/wp\-json\/#i'  );
                    $is_sitemap = is_server_val('SCRIPT_URL', '#sitemap\.xml$#i' ) || is_server_val('REDIRECT_SCRIPT_URL',  '#sitemap\.xml$#i' );
                    $is_article = is_server_val('SCRIPT_URL', '#\/$#i'           );  /* RISKY! permalink format specific. */
                    
                    return (  true  === $is_php
                           && false === $is_admin
                           && false === $is_feed
                           && false === $is_atom
                           && false === $is_json
                           && false === $is_sitemap
                           && true  === $is_article
                           );
                  });
  if(false === $is_engine_ok) return;
  /* ---------------------------------------------------------------------------------------------- */


/*╔══════════════════╗
  ║ Modify Raw-HTML. ║
  ╚══════════════════╝*/
  add_action('template_redirect', function (){
    @ob_start(function($html){
    /*────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────*/
    /*╔═════════╗
      ║ protect ║
      ╚═════════╝*/
                $html = call_user_func(function () use($html){            /*    protect pre-tags and code-tags original content.  */
                    $tags_to_protect = [ 'pre'        => '_p_r_e_'
                                       , 'code'     => '_c_o_d_e_'
                                       , 'textarea' => '_t_e_x_t_a_r_e_a_'
                                       ];
                    foreach ($tags_to_protect as $tag => $protected_tag) {
                      $html = preg_replace_callback("#<" . $tag . "(.*?)>(.*?)</" . $tag . ">#is", function ($arr) use ($tag, $protected_tag) {
                        if (!isset($arr[0])) return; /* no found: no add, no delete */
                        $full = $arr[0];
                        return '<' . $protected_tag . '>' . base64_encode(gzcompress($full)) . '</' . $protected_tag . '>'; /*                      clean from HTML. */
                      }, $html);
                    }
                    return $html;
                });
                /*-------------------------------------------------------------------------------------------------------------*/
    /*╔═══════╗
      ║ $html ║
      ╚═══════╝*/
                $html = preg_replace("#(\s*[\"\']{0,1}\s*)tag\-[^\s\"\']+(\s*[\"\']{0,1}\s*)#msi", " $1 $2 ", $html);
                /*-------------------------------------------------------------------------------------------------------------*/
    /*╔═══════════╗
      ║ unprotect ║
      ╚═══════════╝*/
                $html = call_user_func(function () use($html){            /*  unprotect (bring back) pre-tags and code-tags original content. */
                  $tags_to_unprotect = [ '_p_r_e_'
                                       , '_c_o_d_e_'
                                       , '_t_e_x_t_a_r_e_a_'
                                       ];
                  foreach ($tags_to_unprotect as $index => $tag) {
                    $html = preg_replace_callback("#<" . $tag . ">(.*?)</" . $tag . ">#is", function ($arr) use ($tag) {
                      if (!isset($arr[0])) return; /*no found: no add, no delete*/
                      $inline = $arr[1];
                      return gzuncompress(base64_decode($inline)); /*                      clean from HTML. */
                    }, $html);
                  }
                  return $html;
                });
    /*────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────*/
                return $html;
             });
  }, -9999996);

  add_action('shutdown', function () {
    while (ob_get_level() > 0) @ob_end_flush();
  }, +9999996);
});

?>