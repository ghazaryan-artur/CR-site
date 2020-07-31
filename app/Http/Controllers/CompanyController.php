<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;

class CompanyController extends Controller
{
    public function index() {
        $response['title'] = 'Software Development Company - Why Us | CodeRiders';
        $response['description'] = 'We deliver timely and quality custom software development services. Our development team provides valuable solutions to enterprises and other businesses.';

        $clients = array(
            0 => array(
                'text'  => 'CRM and an e-mail marketing system for Worldsoft AG',
                'image' => '/public/images/worldsoft-crm-solution.png',
                'href'  => 'https://worldsoft-wbs.info/'
            ),
            1 => array(
                'text'  => 'Shopping engine and marketplace for Footmall',
                'image' => '/public/images/footmall-e-commerce-solution.png',
                'href'  => 'https://www.footmall.se/'
            ),
            2 => array(
                'text'  => 'YouTube Analytics, Optimization and Tracking SaaS application for Rankify',
                'image' => '/public/images/rankify-analytics-optimization-tracking-saas-app.png',
                'href'  => 'https://novelconcept.org/rankify-analytics/'
            ),
            3 => array(
                'text'  => 'E-commerce Solution with Real Time Dashboards for Abramov Software',
                'image' => '/public/images/portfolio/abramov-software-e-commerce-solution.png',
                'href'  => 'https://www.abramov-software.de/'
            ),
            4 => array(
                'text'  => 'Private Family Cloud Software for Lifestyle Management',
                'image' => '/public/images/portfolio/logo_dwel2.png',
                'href'  => 'https://www.dwel.online/'
            )
        );

        $recent_blogs        = Blog::getByCount(2);

        $response['blogs']   = $recent_blogs;
        $response['clients'] = $clients;
        $response['target_blog']['image']['full_url'] = '/public/images/fbshare/company.jpg';

        return view('Company', $response);
    }
}
