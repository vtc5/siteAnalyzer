<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Default:index.html.twig', array(
                )
        );
    }
    
    /**
     * @Route("/test")
     * @Method({"POST"})
     */
    public function checkAction(Request $request)
    {
        $url = $this->get('request')->request->get('url');
        $ret = $this->testSite($url);
        return $this->render('AppBundle:Default:test.html.twig', array(
                'param'=>$ret)
        );
    }
    
    private function testSite($url) {
      $url = $this->parse_url_if_valid($url);
      if ($url == null) {
        $ret = array('exist'=>false,
          'code'=>404,
          'over'=>false,
          'length'=>0,
          'hostExist'=>false,
          'countHosts'=>0,
          'sitemapExist'=>false);
        return $ret;
      }
      $url=$url.'robots.txt';
      $ch = curl_init($url); 
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0); 
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv 11.0) like Gecko');
      curl_setopt($ch, CURLOPT_TIMEOUT, 120);
      $result = curl_exec($ch);  
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $fileExist = True;
      if ($httpcode == 404) {$fileExist = False;};
      $length = strlen($result);
      $over = False;
      if ($length> 32767) {$over = True;};
      $countHosts = substr_count($result,'Host:');
      $hostExist = ($countHosts>0);
      $sitemapExist = (substr_count($result,'Sitemap:') >0);
      $ret = array('exist'=>$fileExist,
        'code'=>$httpcode,
        'over'=>$over,
        'length'=>$length,
        'hostExist'=>$hostExist,
        'countHosts'=>$countHosts,
        'sitemapExist'=>$sitemapExist);
      return $ret;
    }
    
    function parse_url_if_valid($url)
    {
      // Массив с компонентами URL, сгенерированный функцией parse_url()
      $arUrl = parse_url($url);
      // Возвращаемое значение. По умолчанию будет считать наш URL некорректным.
      $ret = null;

      // Если не был указан протокол, или
      // указанный протокол некорректен для url
      if (!array_key_exists("scheme", $arUrl)
              || !in_array($arUrl["scheme"], array("http", "https")))
          // Задаем протокол по умолчанию - http
          $arUrl["scheme"] = "http";

      // Если функция parse_url смогла определить host
      if (array_key_exists("host", $arUrl) &&
              !empty($arUrl["host"]))
          // Собираем конечное значение url
          $ret = sprintf("%s://%s%s", $arUrl["scheme"],
                          $arUrl["host"], $arUrl["path"]);

      // Если значение хоста не определено
      // (обычно так бывает, если не указан протокол),
      // Проверяем $arUrl["path"] на соответствие шаблона URL.
      else if (preg_match("/^\w+\.[\w\.]+(\/.*)?$/", $arUrl["path"]))
          // Собираем URL
          $ret = sprintf("%s://%s", $arUrl["scheme"], $arUrl["path"]);
      if ($ret != null) {
        if (substr($ret, -1) != '/') {
          $ret .= '/';
        }
      }
      return $ret;
    }    
}
