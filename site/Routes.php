<?php
/**
 * Site Routes - Hotel Booking Platform
 * All routes are defined here
 */

use Shared\Core\Router;
use Site\Controllers\HomeController;
use Site\Controllers\HotelController;
use Site\Controllers\SearchController;
use Site\Controllers\BookingController;
use Site\Controllers\AuthController;

// Public routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/hotels', [HotelController::class, 'index']);
$router->get('/hotels/{city_slug}', [HotelController::class, 'index']);
$router->get('/hotel/{slug}', [HotelController::class, 'show']);
$router->get('/hotel/{slug}/{room}', [HotelController::class, 'room']);
$router->get('/search', [SearchController::class, 'index']);
$router->get('/api/search', [SearchController::class, 'api']);
$router->get('/api/autocomplete', [SearchController::class, 'autocomplete']);

// Auth routes
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Booking routes (authenticated)
$router->get('/booking/new', [BookingController::class, 'new']);
$router->post('/booking/create', [BookingController::class, 'create']);
$router->get('/booking/{token}', [BookingController::class, 'show']);
$router->post('/booking/{token}/refresh', [BookingController::class, 'refresh']);
$router->get('/booking/{token}/pay', [BookingController::class, 'pay']);
$router->get('/booking/confirm/{code}', [BookingController::class, 'confirm']);
$router->get('/booking/track', [BookingController::class, 'track']);

// API v1 Public
$router->get('/api/v1/public/hotels', [ApiHotelController::class, 'index']);
$router->get('/api/v1/public/hotels/{slug}', [ApiHotelController::class, 'show']);
$router->get('/api/v1/public/search', [SearchController::class, 'api']);

// API v1 Reservations
$router->get('/api/v1/reservations/{token}/heartbeat', [BookingController::class, 'heartbeat']);
$router->post('/api/v1/reservations/{token}/refresh', [BookingController::class, 'refresh']);

// SEO
$router->get('/sitemap.xml', [HomeController::class, 'sitemapIndex']);
$router->get('/sitemap-hotels.xml', [HomeController::class, 'sitemapHotels']);
$router->get('/sitemap-blog.xml', [HomeController::class, 'sitemapBlog']);
$router->get('/robots.txt', [HomeController::class, 'robots']);

// Blog
$router->get('/blog', [HomeController::class, 'blogIndex']);
$router->get('/blog/{slug}', [HomeController::class, 'blogShow']);

// Static pages
$router->get('/about', [HomeController::class, 'page']);
$router->get('/contact', [HomeController::class, 'page']);
$router->get('/terms', [HomeController::class, 'page']);
$router->get('/privacy', [HomeController::class, 'page']);
$router->get('/faq', [HomeController::class, 'page']);

// Cities
$router->get('/city/{slug}', [HotelController::class, 'city']);

// Dashboard (redirect to admin)
$router->get('/dashboard', [\Site\Controllers\AdminController::class, 'index']);

// Admin routes (separate from CRM)
$router->get('/admin', [\Site\Controllers\AdminController::class, 'index']);
$router->get('/admin/database', [\Site\Controllers\AdminController::class, 'database']);

// Admin Hotel Management
$router->get('/admin/hotels', [\Site\Controllers\AdminHotelController::class, 'index']);
$router->get('/admin/hotels/{id}/edit', [\Site\Controllers\AdminHotelController::class, 'edit']);
$router->post('/admin/hotels/{id}/update', [\Site\Controllers\AdminHotelController::class, 'update']);
$router->get('/admin/hotels/{id}/rooms', [\Site\Controllers\AdminHotelController::class, 'rooms']);
$router->post('/admin/hotels/{id}/rooms/add', [\Site\Controllers\AdminHotelController::class, 'addRoom']);
$router->get('/admin/rooms/{id}/edit', [\Site\Controllers\AdminHotelController::class, 'roomEdit']);
$router->post('/admin/rooms/{id}/update', [\Site\Controllers\AdminHotelController::class, 'roomUpdate']);

// 404
$router->set404([HomeController::class, 'notFound']);
