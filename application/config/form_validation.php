<?php

$config = array(

        /**
         * API:用户列表
         */
        
        'api/user/index_get' => array(
            array(
                'field' => 'test',
                'label' => 'test',
                'rules' => array(
                    'required',
                    array(
                        // Check validity of $str and return TRUE or FALSE
                        'valid_json',
                        function($str)
                        {
                            if (!json_decode($str)){
                                return false;
                            }else {
                                return true;
                            }
                        }
                    )
                ),
                'errors' => array(
                    'required'   => 'You must provide a {field}.',
                ),
            ),

            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            )
        ),


        /**
         * API:用户注册
         */
        
        'api/user/register_post' => array(
            array(
                'field' => 'username',
                'label' => 'username',
                'rules' => array('required','username_check','is_unique[users.username]')
            ),

            array(
                'field' => 'password',
                'label' => 'password',
                'rules' => array(
                    'required',
                    'password_check',
                )
            ),

            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => array('required','valid_email','is_unique[users.email]')
            )
        ),


        /**
         * API:忘记密码，发送电子邮件
         */
        'api/user/forgot_pwd_post' => array(
            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => array('required','valid_email','is_existent[users.email]')
            )
        ), 

        /**
         * API:邮箱和密码登录
         */
        'api/user/common_login_post' => array(
            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => array('required','valid_email','is_existent[users.email]')
            ),

            array(
                'field' => 'password',
                'label' => 'password',
                'rules' => array(
                    'required'
                )
            ),
        ),



        /**
         * API:用户退出登录
         */
        'api/user/logout_delete' => array(
            array(
                'field' => 'device_token',
                'label' => 'device_token',
                'rules' => array('trim')
            )
        ),



        /**
         * API:获取用户信息接口
         */
        'api/user/user_info_get' => array(
            array(
                'field' => 'field',
                'label' => 'field',
                'rules' => array('trim')
            ),
            array(
                'field' => 'user_unique_key',
                'label' => 'user_unique_key',
                'rules' => array('trim', 'required')
            ),
        ),        


        /**
         * API:修改用户信息接口
         */
        'api/user/profile_post' => array(
            array(
                'field' => 'bio',
                'label' => 'bio',
                'rules' => array('trim')
            ),
            array(
                'field' => 'signature',
                'label' => 'signature',
                'rules' => array('trim')
            ),

            array(
                'field' => 'username',
                'label' => 'username',
                'rules' => array('trim','username_check','is_unique[users.username]')
            ),
        ),


        /**
         * API:修改用户帐户接口
         */
        'api/user/account_post_00' => array(
            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => array('valid_email','is_unique[users.email]')
            )
        ),

        /**
         * API:修改用户帐户接口 - 分支1
         */
        'api/user/account_post_01' => array(
            array(
                'field' => 'new_password',
                'label' => 'new_password',
                'rules' => array(
                    'required',
                    'password_check',
                )
            ),
            array(
                'field' => 'new_password_repeat',
                'label' => 'new_password_repeat',
                'rules' => array('required', 'matches[new_password]'),
            ),
        ),



        /**
         * API:修改用户帐户接口
         */
        'api/user/user_sns_delete' => array(
            array(
                'field' => 'third_platform',
                'label' => 'third_platform',
                'rules' => array('required','trim')
            )
        ),



        /**
         * API:微信注册
         */
        'api/user/wechat_register_post' => array(
            array(
                'field' => 'access_token',
                'label' => 'access_token',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),
            
            array(
                'field' => 'openid',
                'label' => 'openid',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),

            array(
                'field' => 'refresh_token',
                'label' => 'refresh_token',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),

            array(
                'field' => 'username',
                'label' => 'username',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),

            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => array(
                    'trim',
                    'required',
                    'valid_email',
                    'is_unique[users.email]'
                ),
                'errors' => array(
                    'is_unique' => '您的邮箱在系统中已存在，您可以选择用该邮箱登陆后绑定微信账号', //这儿是否可以直接绑定？
                ),
            ),

            array(
                'field' => 'password',
                'label' => 'password',
                'rules' => array(
                    'trim',
                    'required',
                    'password_check',
                )
            ),

            array(
                'field' => 'expires_in',
                'label' => 'expires_in',
                'rules' => array(
                    'trim',
                    'required',
                    'is_natural_no_zero'
                )
            )
        ),

    
        /**
         * API:微信登陆
         */
        'api/user/wechat_login_post' => array(
            array(
                'field' => 'access_token',
                'label' => 'access_token',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),
            
            array(
                'field' => 'openid',
                'label' => 'openid',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),

            array(
                'field' => 'refresh_token',
                'label' => 'refresh_token',
                'rules' => array(
                    'trim',
                    'required',
                )
            ),

            array(
                'field' => 'expires_in',
                'label' => 'expires_in',
                'rules' => array(
                    'trim',
                    'required',
                    'is_natural_no_zero'
                )
            )
        ),


        ////////api.common///////////



        /**
         * API:记录设备信息
         */
        
        'api/common/device_post' => array(
            array(
                'field' => 'device_token',
                'label' => 'device_token',
                'rules' => array(
                    'trim'
                )
            )
        ),




        
        /**
         * API:公开课程列表
         */
        
        'api/course/list_get' => array(
            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'limit',
                'label' => 'limit',
                'rules' => array(
                    'is_natural'
                )
            ),
        ),



        /**
         * API:我的课程列表
         */
        
        'api/course/my_list_get' => array(
            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'limit',
                'label' => 'limit',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'status',
                'label' => 'status',
                'rules' => array(
                    'trim'
                )
            ),
        ),


        /**
         * 老师已发布的课程列表
         */
        'api/course/pub_courses_get' => array(
            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'limit',
                'label' => 'limit',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'user_unique_key',
                'label' => 'user_unique_key',
                'rules' => array(
                    'required'
                )
            ),
        ),



        /**
         * API:获取一个用户回答的填空题答案
         */
        'api/quiz/answers_gap_get' => array(
            array(
                'field' => 'question_unique_key',
                'label' => 'question_unique_key',
                'rules' => array(
                    'required',
                    'is_existent[questions.unique_key]'
                )
            ),
            
            array(
                'field' => 'time_created',
                'label' => '答题时间',
                'rules' => array(
                    'required',
                    'is_existent[user_question_relations_gaps.time_created]'
                )
            )
        ),


        /**
         * API:获取一个填空题的详细信息
         */
        'api/quiz/gap_get' => array(
            array(
                'field' => 'question_unique_key',
                'label' => 'question_unique_key',
                'rules' => array(
                    'required'
                )
            )
        ),


        /**
         * API:获取一个选择题的详细信息
         */
        'api/quiz/multiple_options_get' => array(
            array(
                'field' => 'question_unique_key',
                'label' => 'question_unique_key',
                'rules' => array(
                    'required'
                )
            )
        ),


        /**
         * API:获取用户的购买历史记录
         */
        'api/payment/history_get' => array(
            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'limit',
                'label' => 'limit',
                'rules' => array(
                    'is_natural'
                )
            ),
        ),



        /**
         * API:获取用户的通知列表
         */
        'api/notify/list_get' => array(
            array(
                'field' => 'offset',
                'label' => 'offset',
                'rules' => array(
                    'is_natural'
                )
            ),
            array(
                'field' => 'limit',
                'label' => 'limit',
                'rules' => array(
                    'is_natural'
                )
            ),
        ),


        /**
         * API:标记我的通知已读状态
         */
        'api/notify/mark_post' => array(
            array(
                'field' => 'notify_id',
                'label' => 'notify_id',
                'rules' => array(
                    'is_natural',
                    'required'
                )
            ),
        ),

);