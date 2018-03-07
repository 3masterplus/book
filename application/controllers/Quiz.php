<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Quiz extends MY_Controller
	{
		function __construct()
		{
			parent::__construct();
			
			$this->members_only();
			$this->load->library('quiz_lib');
			$this->load->library('form_validation');
		}	
		
		//获取一个用户某次填空题的答案
		function ajax_get_answers_gaps()
		{
			if(CI_POST('get_answers_gaps'))
			{
				$time_created			= CI_POST('time_created');
				$question_unique_key	= CI_POST('question_unique_key');
				
				$this->form_validation->set_rules('time_created', '答题时间', 'required|callback_is_existent[user_question_relations_gaps.time_created]');
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required|callback_is_existent[questions.unique_key]');
				
				if($this->form_validation->run())
				{
					$question_guid = $this->my_lib->get_guid_by_unique_key($question_unique_key);
					$condition = array('time_created' => $time_created, 'user_guid' => $this->_guid, 'question_guid' => $question_guid);
					
					$fields = array('gap_key', 'answer', 'result');
					$result = $this->my_lib->get_records('user_question_relations_gaps', $fields, $condition);
					
					$this->ajax_ini($result);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		/**
		//创建一个带有选择的填空题（完型填空选择题）
		function ajax_create_a_question_with_gaps_and_options()
		{
			if(CI_POST('create_a_question_with_cap_options'))
			{
				$quiz_unique_key	= CI_POST('quiz_unique_key');
				$node_unique_key 	= CI_POST('node_unique_key');
				$section_unique_key = CI_POST('section_unique_key');
				$course_unique_key	= CI_POST('course_unique_key');
				$question_main		= CI_POST('question_main');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->add_a_question_with_gaps_and_options($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $question_main);
						
					if(count($result) > 0)
					{
						$this->ajax_ini($result);
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
			
				$this->ajax_response();
			}
		}
		*/
		
		/***
		//为一个填空选择题添加答案
		function ajax_add_options_to_a_gap()
		{
			if(CI_POST('add_options_to_a_gap'))
			{
				$question_unique_key = CI_POST('question_unique_key');
			}
		}
		
		*/
		
		//创建一个填空题
		function ajax_create_a_question_with_gaps()
		{	
			if(CI_POST('create_a_question_with_gaps'))
			{	
				$quiz_unique_key	= CI_POST('quiz_unique_key');
				$node_unique_key 	= CI_POST('node_unique_key');
				$section_unique_key = CI_POST('section_unique_key');
				$course_unique_key	= CI_POST('course_unique_key');
				$question_main		= CI_POST('question_main','<img>');
				$weight				= CI_POST('weight');
				$gaps				= (CI_POST('gaps')) ? CI_POST('gaps') : array();
				
				$this->form_validation->set_rules('quiz_unique_key', 'quiz_unique_key', 'required|callback_is_existent[quizzes.unique_key]');
				$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required|callback_is_existent[nodes.unique_key]');
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('question_main', '题干', 'required');
				$this->form_validation->set_rules('weight', '排序', 'required');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->add_a_question_with_gaps($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $question_main, $gaps, $weight);
						
					if(count($result) > 0)
					{
						$this->gate_for_ajax($course_unique_key, $this->_guid);
						
						$node_guid 		= $this->my_lib->get_guid_by_unique_key($node_unique_key);
						$section_guid 	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
						$course_guid 	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
						
						//记录该节点、课节、以及课程插入了一张图片
						$this->count_lib->plus($node_guid, 'question');
						$this->count_lib->plus($section_guid, 'question');
						$this->count_lib->plus($course_guid, 'question');
					
						$this->ajax_ini($result);
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
			
				$this->ajax_response();
			}
		}
		
		// 创建一个多选选择题（单选/多选）
		function ajax_create_a_question_with_multiple_options()
		{
			if(CI_POST('create_a_question_with_multiple_options'))
			{
				$quiz_unique_key	= CI_POST('quiz_unique_key');
				$node_unique_key 	= CI_POST('node_unique_key');
				$section_unique_key = CI_POST('section_unique_key');
				$course_unique_key	= CI_POST('course_unique_key');
				$question_weight	= CI_POST('question_weight');
				$question_main		= CI_POST('question_main');
				$options			= CI_POST('options');
				
				$this->form_validation->set_rules('quiz_unique_key', 'quiz_unique_key', 'required|callback_is_existent[quizzes.unique_key]');
				$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required|callback_is_existent[nodes.unique_key]');
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('question_main', '题干', 'required');
				$this->form_validation->set_rules('question_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->add_a_question_with_options($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $question_main, $question_weight, $options);
					
					if(count($result) > 0)
					{
						$this->gate_for_ajax($course_unique_key, $this->_guid);
						
						$node_guid 		= $this->my_lib->get_guid_by_unique_key($node_unique_key);
						$section_guid 	= $this->my_lib->get_guid_by_unique_key($section_unique_key);
						$course_guid 	= $this->my_lib->get_guid_by_unique_key($course_unique_key);
						
						//记录该节点、课节、以及课程插入了一张图片
						$this->count_lib->plus($node_guid, 'question');
						$this->count_lib->plus($section_guid, 'question');
						$this->count_lib->plus($course_guid, 'question');
						
						$this->ajax_ini($result);
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		// 根据问题的“unique_key”，获取一个问题的全部信息
		function ajax_get_a_question_with_multiple_options()
		{
			if(CI_POST('get_a_question_with_multiple_options'))
			{
				$question_unique_key = CI_POST('question_unique_key');
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->get_a_question_with_multiple_options($question_unique_key);
					$data['options'] = $result;
					$this->ajax_ini($data);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//获取一道填空题
		function ajax_get_a_question_with_gaps()
		{
			if(CI_POST('get_a_question_with_gaps'))
			{
				$question_unique_key = CI_POST('question_unique_key');
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->get_a_question_with_gaps($question_unique_key);
					$this->ajax_ini(array('gaps' => $result));
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX创建一个测试
		function ajax_create_a_quiz()
		{
			if(CI_POST('create_a_quiz'))
			{
				$node_unique_key 	= CI_POST('node_unique_key');
				$section_unique_key = CI_POST('section_unique_key');
				$course_unique_key	= CI_POST('course_unique_key');
				$quiz_title			= CI_POST('quiz_title');
				$quiz_main			= CI_POST('quiz_main');
				$quiz_weight		= CI_POST('quiz_weight');
				
				$this->form_validation->set_rules('node_unique_key', 'node_unique_key', 'required|callback_is_existent[nodes.unique_key]');
				$this->form_validation->set_rules('section_unique_key', 'section_unique_key', 'required|callback_is_existent[sections.unique_key]');
				$this->form_validation->set_rules('course_unique_key', 'course_unique_key', 'required|callback_is_existent[courses.unique_key]');
				$this->form_validation->set_rules('quiz_title', '测试标题', 'required');
				$this->form_validation->set_rules('quiz_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					$quiz_unique_key = $this->quiz_lib->add_a_quiz($course_unique_key, $section_unique_key, $node_unique_key, $quiz_title, $quiz_main, $quiz_weight);
					$this->ajax_ini(array('quiz_unique_key' => $quiz_unique_key));
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX重新设置一个测试的排序值
		function ajax_resort_quiz()
		{
			if(CI_POST('resort_quiz'))
			{
				$quiz_unique_key = CI_POST('quiz_unique_key');
				$quiz_new_weight = CI_POST('quiz_new_weight');
				
				$this->form_validation->set_rules('quiz_unique_key', 'node_unique_key', 'required|callback_is_existent[quizzes.unique_key]');
				$this->form_validation->set_rules('quiz_new_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					$data = array('weight' => $quiz_new_weight);
					$condition = array('unique_key' => $quiz_unique_key);
					$this->my_lib->update_records('entities', $data, $condition);
					$this->ajax_ini();
				}
				else
					$this->_ajax_message = $this->my_lib->generate_error_message();
				
				$this->ajax_response();
			}	
		}
		
		//AJAX排序选择问题
		function ajax_resort_multiple_question()
		{
			if(CI_POST('resort_multiple_question'))
			{
				$question_unique_key 	= CI_POST('question_unique_key');
				$question_new_weight	= CI_POST('question_new_weight');
				
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required');
				$this->form_validation->set_rules('question_new_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($course_unique_key, $this->_guid);
					$data = array('weight' => $question_new_weight);
					$condition = array('unique_key' => $question_unique_key);
					$this->my_lib->update_records('entities', $data, $condition);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX排序选择问题的答案
		function ajax_resort_multiple_question_option()
		{
			if(CI_POST('resort_multiple_question_option'))
			{
				$option_id 			= CI_POST('option_id');
				$option_new_weight	= CI_POST('option_new_weight');
				
				$this->form_validation->set_rules('option_id', 'option_id', 'required');
				$this->form_validation->set_rules('option_new_weight', '排序值', 'required');
				
				if($this->form_validation->run())
				{
					$data = array('weight' => $option_new_weight);
					$condition = array('id' => $option_id);
					$this->my_lib->update_records('options', $data, $condition);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX更新一个问题
		function ajax_update_a_question_with_multiple_options()
		{
			if(CI_POST('update_a_question_with_multiple_options'))
			{
				$question_unique_key 	= CI_POST('question_unique_key');
				$question				= CI_POST('question');
				
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required|callback_is_existent[questions.unique_key]');
				//$this->form_validation->set_rules('question', '问题', 'required');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->update_a_question_with_options($question_unique_key, $question);
					if($result['result'])
					{
						$this->ajax_ini($result['data']);
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX更新一个填空题
		function ajax_update_a_question_with_gaps()
		{
		
			$question = array(
				'question_main' => 'sssssssssss',
				'gaps' => array(
				
					'deleted' => array('key1', 'key2', 'key3'),
					'updated' => array(
						array(
							'key' => 'key4',
							'answer' => '1234',
							'explanation' => '4567'
						),
					
						array(
							'key' => 'key5',
							'answer' => '123445',
							'explanation' => '456ssss7'
						)
					),
				
					'added' => array(
						array(
							'key' => 'key6',
							'answer' => '1234',
							'explanation' => '4567'
						),
					
						array(
							'key' => 'key7',
							'answer' => '123445',
							'explanation' => '456ssss7'
						)
					)
				
				)
			);
		
		
			if(CI_POST('update_a_question_with_gaps'))
			{
				$question_unique_key 	= CI_POST('question_unique_key');
				$question				= CI_POST('question');
				
				$this->form_validation->set_rules('question_unique_key', 'question_unique_key', 'required|callback_is_existent[questions.unique_key]');
				
				if($this->form_validation->run())
				{
					$result = $this->quiz_lib->update_a_question_with_gaps($question_unique_key, $question);
					
					if($result)
					{
						$this->ajax_ini();
					}
					else
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//AJAX更新一个测试
		function ajax_update_a_quiz()
		{
			if(CI_POST('update_a_quiz'))
			{
				$quiz_unique_key 	= CI_POST('quiz_unique_key');
				$quiz_title			= CI_POST('quiz_title');
				$quiz_main			= CI_POST('quiz_main');
				
				$this->form_validation->set_rules('quiz_unique_key', 'quiz_unique_key', 'required|callback_is_existent[quizzes.unique_key]');
				$this->form_validation->set_rules('quiz_title', '测验标题', 'required');
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($quiz_unique_key, $this->_guid);
					$data = array('title' => $quiz_title, 'main' => $quiz_main);
					$quiz_unique_key = $this->quiz_lib->update_a_quiz($quiz_unique_key, $data);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}	
		}
	}