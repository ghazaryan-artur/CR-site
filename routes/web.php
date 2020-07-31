<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();
Route::group(['prefix' => 'admin',  'middleware' => 'auth'], function(){
    Route::get('/blog', 'Admin\AdminBlogController@index');
    Route::get('/blog/list', 'Admin\AdminBlogController@show');//may be post
    Route::get('/blog/create', function(){
        $response['page_title'] = "Blog Create";
        $response['admin'] = true;
        $response['admin_login'] = false;
        view('createBlog', $response);
    });
    Route::post('/admin/blog/create/new', 'Admin\AdminBlogController@create');
});


Route::get('', 'HomeController@index');

Route::get('/blog', 'BlogController@index');
Route::get('/blog/{slug}', 'BlogController@show');

Route::get('company-why-us', 'CompanyController@index');

Route::prefix('/services')->group(function () {
    Route::get('', function(){
        $response['title'] = 'Web, Mobile and Custom Software Development Services | CodeRiders';
        $response['description'] = 'Professional software development services offering web/mobile development and design, custom software development, software outsourcing and IT consulting.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.servImg');
        return view('services', $response);
    });
    Route::get('/web-development-and-design', function(){
        $response['title'] = 'Web development and Design Services | CodeRiders';
        $response['description'] = 'We deliver web development and design services, including website migration and integration, migration to cloud, compliance with SEO standards, etc.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.servImg');
        return view('developmenAndDesign', $response);
    });
    Route::get('/mobile-app-development', function(){
        $response['title'] = 'Mobile App Development Services | CodeRiders';
        $response['description'] = 'Experts in mobile app development services such as native and cross-platform mobile development, wire framing and custom design architecture, UI/UX, etc.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.servImg');
        return view('mobileDev', $response);
    });
    Route::get('/custom-software-development', function(){
        $response['title'] = 'Custom Software Development Services | CodeRiders';
        $response['description'] = 'Delivery of high level custom software development services, including enterprise solutions and product development, project recovery, software upgrade, etc';
        $response['target_blog']['image']['full_url'] = Config::get('constants.servImg');
        return view('customDev', $response);
    });
    Route::get('/software-development-outsourcing-and-it-consulting', function(){
        $response['title'] = 'Software Development Outsourcing, IT Consulting | CodeRiders';
        $response['description'] = 'We can completely outsource your software development project or provide IT consulting to make better decisions.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.servImg');
        return view('outsors', $response);
    });
});

Route::prefix('/solutions')->group(function () {
    Route::get('', function(){
        $response['title'] = 'Business Software Solutions | CodeRiders';
        $response['description'] = 'Custom software solutions for business. Creating e-Commerce, BI, CRM solutions, integrating APIs, developing big data analytics and real time solutions.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('solutions', $response);
    });
    Route::get('/integration-software', function(){
        $response['title'] = 'Integration Software | CodeRiders';
        $response['description'] = 'We provide high-quality software integration services like social media APIs, legacy system integrations, order processing,data integration, mobile APIs.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('apiIntegration', $response);
    });
    Route::get('/ecommerce-solutions-development', function(){
        $response['title'] = 'E-commerce Solutions Development | CodeRiders';
        $response['description'] = 'Custom e-Commerce solutions development for small, mid-sized businesses and enterprises. Experts in website design and development, eCommerce module, etc.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('E-Commerce', $response);
    });
    Route::get('/business-intelligence-solution-development', function(){
        $response['title'] = 'Business Intelligence Solution Development I BI | CodeRiders';
        $response['description'] = 'We offer BI solutions like forecasting and analysis, structured data, optimization, budget planning, financial reporting, data visualization and many more.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('BI', $response);
    });
    Route::get('/crm-solution-development', function(){
        $response['title'] = 'CRM Solution Development | CodeRiders';
        $response['description'] = 'We offer Customer Relationship Management development services including mobile CRM, marketing automation, customer experience management, etc.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('CRM', $response);
    });
    Route::get('/real-time-dashboards', function(){
        $response['title'] = 'Real-Time Reporting with Business Dashboards | CodeRiders';
        $response['description'] = 'Our real-time dashboards cover all your visualization dreams including infrastructure-wide visibility, smart visualizations, metric transforms, etc.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('realTime', $response);
    });
    Route::get('/big-data-and-analytics-software-solution', function(){
        $response['title'] = 'Big Data & Analytics Software Solutions | CodeRiders';
        $response['description'] = 'We provide big data and analytics software development solutions including data visualization, segmentation & clustering, and predictive analytics.';
        $response['target_blog']['image']['full_url'] = Config::get('constants.solImg');
        return view('bigDataAnalitics', $response);
    });
});

Route::get('/software-development-process', function(){
    $response['title'] = 'Software Development Process and Approach | CodeRiders';
    $response['description'] = 'Explore most successful software development processes and methodologies to improve business efficiency. We offer Industry standard engagement models.';
    $response['target_blog']['image']['full_url'] = '/public/images/fbshare/process.jpg';
    return view('processes', $response);
});
Route::get('/industries', function(){
    $response['title'] = 'Software Solutions for All Types of Industries | CodeRiders';
    $response['description'] = 'Find your Healthcare, Legal, Finance, Entertainment, Retail and Wholesale software development provider at CodeRiders!';
    $response['target_blog']['image']['full_url'] = '/public/images/fbshare/industries.jpg';
    return view('industries', $response);
});
Route::get('/portfolio', function(){
    $response['title'] = 'Portfolio, Software Development Case Studies | CodeRiders';
    $response['description'] = 'Explore CodeRiders\' expertise in delivering quality software development services for SMB\'s and enterprises worldwide.';
    $response['target_blog']['image']['full_url'] = '/public/images/fbshare/portfolio.jpg';
    return view('portfolio', $response);
});
Route::get('/privacy-policy', function(){
    $response['title'] = 'Privacy Policy | CodeRiders';
    $response['description'] = 'CodeRiders privacy policies regarding the collection, use, and disclosure of personal data when you use our service.';'Explore CodeRiders\' expertise in delivering quality software development services for SMB\'s and enterprises worldwide.';
    $response['target_blog']['image']['full_url'] = '/public/images/fbshare/portfolio.jpg';
    return view('privacyPolicy', $response);
});



Route::get('/a', 'ContactUsController@send');
//Auth::routes();

// temporary route
Route::get('/admin/blog', 'Admin\AdminBlogController@index');
Route::get('/cr', 'Admin\AdminBlogController@create');

// sendgreed tests 1
//Route::get('/b', 'SomeController@sendEmail');


//sendgreed tests 2
//Route::any ( 'sendemail', function () {
//    if (Request::get ( 'message' ) != null) {
//        $data = array(
//            'bodyMessage' => Request::get('message')
//        );
//    } else {
//        $data [] = '';
//        Mail::send('email', $data, function ($message) {
//
//            $message->from('ghazaryan.artur1@gmail.com', 'Just Laravel');
//
//            $message->to(Request::get('toEmail'))->subject('Just Laravel demo email using SendGrid');
//        });
//    }
////    return Redirect::back ()->withErrors ( [
////        'Your email has been sent successfully'
////    ] );
//    return view('emails.test')->with('res', 'Your email has been sent successfully');
//} );



