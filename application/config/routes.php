<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['course/([a-zA-Z0-9_-]{2,32}+)/setting'] = "build/set_a_course/$1";
$route['course/([a-zA-Z0-9_-]{2,32}+)/setting/fee'] = "build/set_fee/$1";
$route['course/([a-zA-Z0-9_-]{2,32}+)/setting/section'] = "build/set_a_section/$1";
$route['course/([a-zA-Z0-9_-]{2,32}+)/setting/image'] = "build/set_image/$1";
$route['course'] = "course/list_all_available_courses";

$route['course/([a-zA-Z0-9_-]{2,32}+)/home'] = "course/get_course_home/$1";

$route['user/account/edit'] = "user/edit_account";
$route['user/profile/edit'] = "user/edit_profile";
$route['notifications'] = "notification";
$route['dashboard'] = "course/dashboard";
$route['dashboard/completed'] = "course/dashboard/completed";
$route['dashboard/paid'] = "course/dashboard/paid";
$route['dashboard/home'] = "course/dashboard/home";

/**
 * API路由规则
 */
$route['api/v1/course/([a-zA-Z0-9_-]{2,32}+)/detail']              = "api/v1/course/course/$1";  //课程基本信息
$route['api/v1/course/([a-zA-Z0-9_-]{2,32}+)/sections']            = "api/v1/course/sections_list/$1"; //某课程章节列表
$route['api/v1/course/section/([a-zA-Z0-9_-]{2,32}+)/nodes']       = "api/v1/course/nodes_list/$1"; //某章节节点列表
$route['api/v1/course/section/node/([a-zA-Z0-9_-]{2,32}+)/detail'] = "api/v1/course/node_detail/$1"; //节点详情
$route['api/v1/course/([a-zA-Z0-9_-]{2,32}+)/join']                = "api/v1/course/join_course/$1"; //加入一门课程
$route['api/v1/course/([a-zA-Z0-9_-]{2,32}+)/pub_courses']         = "api/v1/course/pub_courses/$1"; //老师发布的课程列表
$route['api/v1/payment/([a-zA-Z0-9_-]{2,32}+)/status']             = "api/v1/payment/status/$1"; //获取订单的支付状态