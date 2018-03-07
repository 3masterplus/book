<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Quiz_lib extends my_lib
	{
		function __construct()
		{
			parent::__construct();	
		}
		
		//获取一个问题的全部信息，包括：提干、选择、答案、说明等等问题
		function get_a_question($question_guid)
		{
			$data = array();
		
			//获取问题
			$sql = "SELECT entities.main, questions.type FROM entities LEFT JOIN questions ON entities.guid = questions.guid where entities.guid = $question_guid";
			$result = $this->get_records_with_sql($sql);
			
			$question = $result[0];
			
			$data['question'] = $question;
			
			//获取备选答案或答案
			if($question['type'] == 'OPTION')
			{
				$options = $this->get_multiple_options($question_guid);
				
				$number_of_correct_options = 0;
				
				foreach($options AS $row)
				{
					if($row['is_correct'] == 1)
					{
						$number_of_correct_options++;
					}
				}
				
				$data['options'] = $options;
				$data['number_of_correct_options'] = $number_of_correct_options;
			}
			elseif($question['type'] == 'GAP')
			{
				$gaps = $this->get_gaps($question_guid);
				$data['gaps'] = $gaps;
			}
			
			return $data;
		}
		
		
		//回答一个问题
		function answer_a_question($question_guid, $question_type, $quiz_guid, $course_guid, $data)
		{
			if($question_type == 'OPTION')
			{
				$option_id_array = explode(',', $data['answer']);
				$result = $data['result'];
				$this->create_user_question_relations_multiple_options($this->_guid, $option_id_array, $result, $question_guid, $quiz_guid, $course_guid);
			}
			elseif($question_type == 'GAP')
			{
				foreach($data AS $row)
				{
					$gap_key = $row['key'];
					$answer = $row['answer'];
					$result = iif($row['is_correct'], 'T', 'F');
					$time_created = time();
					$this->create_user_question_relations_gaps($this->_guid, $time_created, $gap_key, $answer, $result, $question_guid, $quiz_guid, $course_guid);
				}
			}
			
			$data = serialize($data);
			$related_guids = array('related_guid_1' => $question_guid);
			//$this->ci->river_lib->logit('answer_a_question', $this->_guid, $this->_user_unique_key, $related_guids, $data);
		}
		
		//获取一个用户回答某个问题的最后一次答题历史
		function get_answer_history($user_guid, $question_guid, $question_type = 'option')
		{
			if(strtoupper($question_type) == 'OPTION')
			{
				$sql = "SELECT time_created, result, data FROM user_question_relations_multiple_options ";
				$sql.= "WHERE user_guid = $user_guid AND question_guid = $question_guid ";
				$sql.= "GROUP BY time_created ORDER BY time_created DESC limit 1";
				
				$result = $this->get_records_with_sql($sql);
				
				if(count($result) > 0)
				{
					return $result[0];
				}
			}
			elseif(strtoupper($question_type) == 'GAP' AND $this->check_a_record('user_question_relations_gaps', array('user_guid' => $user_guid, 'question_guid' => $question_guid)))
			{
				$sql = "SELECT time_created FROM user_question_relations_gaps ";
				$sql.= "WHERE user_guid = $user_guid AND question_guid = $question_guid ";
				$sql.= "GROUP BY time_created ORDER BY time_created DESC LIMIT 1";
				
				$result = $this->get_records_with_sql($sql);
				$time_created = $result[0]['time_created'];
				
				$data = array(
					'time_created' 	=> $time_created,
					'answers'		=> $this->get_records('user_question_relations_gaps', array('result'), array('time_created' => $time_created))
				);
				
				return $data;
			}
			
			return array();
		}
		
		//创建用户回答选择题的记录
		private function create_user_question_relations_multiple_options($user_guid, $option_id_array, $result, $question_guid, $quiz_guid, $course_guid)
		{
			$time_created = time();
			
			$answer_data = array();
			
			foreach($option_id_array AS $option_id)
			{
				$answer_data[] = $option_id;
			}
			
			foreach($option_id_array AS $option_id)
			{	
				$data = array(
					'user_guid' 	=> $this->_guid,
					'option_id'		=> $option_id,
					'question_guid'	=> $question_guid,
					'quiz_guid'		=> $quiz_guid,
					'course_guid' 	=> $course_guid,
					'time_created'	=> $time_created,
					'result'		=> iif($result == 'correct', 'T', 'F'),
					'data'			=> serialize($answer_data)
				);
				
				$data['is_correct'] = $this->get_a_value('options', 'is_correct', array('id' => $option_id));
				$this->create_a_record('user_question_relations_multiple_options', $data);
			}
			
			return TRUE;
		}
		
		//创建用户回答填空题的记录
		private function create_user_question_relations_gaps($user_guid, $time_created, $gap_key, $answer, $result, $question_guid, $quiz_guid, $course_guid)
		{
			$data = array(
				'user_guid'		=> $user_guid,
				'time_created' 	=> $time_created, 
				'gap_key'		=> $gap_key,
				'answer'		=> $answer,
				'result' 		=> $result,
				'question_guid' => $question_guid,
				'quiz_guid' 	=> $quiz_guid,
				'course_guid' 	=> $course_guid
			);
			
			return $this->create_a_record('user_question_relations_gaps', $data);
		}
		
		//获取某个“quiz”下的第一个问题的“guid”
		function get_first_question($quiz_guid)
		{
			$condition = array('quiz_guid' => $quiz_guid);
			$fields = array('guid');
			
			$sql = "SELECT entities.guid, questions.guid FROM entities ";
			$sql.= "JOIN questions ON entities.guid = questions.guid ";
			$sql.= "WHERE questions.quiz_guid = $quiz_guid ORDER by entities.weight ASC";
			
			$result = $this->get_records_with_sql($sql);
			
			if(count($result) > 0)
			{
				return $result[0]['guid'];
			}
			else
			{
				return 0;
			}
		}
		
		//获取一个节点下的“quiz”列表和每个“quiz”所包含的问题（questions）
		function get_quizzes($node_guid)
		{
			$quizzes_arr = array();
			
			$fields 	= array('guid', 'unique_key', 'title', 'main', 'weight');
			$condition 	= array('father_guid' => $node_guid, 'subtype_id' => $this->get_subtype_id('quiz'));
			$quizzes 	= $this->get_records('entities', $fields, $condition, NULL, 0, 'weight', 'ASC');
			
			$count = 0;
			
			foreach($quizzes AS $row)
			{
				$quizzes_arr[$count]['guid']		= $row['guid'];
				$quizzes_arr[$count]['unique_key'] 	= $row['unique_key'];
				$quizzes_arr[$count]['title'] 		= $row['title'];
				$quizzes_arr[$count]['main']		= $row['main'];
				$quizzes_arr[$count]['weight']		= $row['weight'];
				
				$questions_arr = array();
				$questions = $this->get_questions($row['guid']);
				
				$questions_count = count($questions);
				
				if($questions_count > 0)
				{
					for($i = 0; $i < $questions_count; $i++)
					{
						$questions_arr[$i] = $questions[$i];
					}
				}
				
				$quizzes_arr[$count]['questions'] = $questions_arr;
				
				$count++;
			}
			
			return $quizzes_arr;
		}
		
		//获取一个“quiz”下的全部问题名称
		function get_questions($quiz_guid)
		{
			$questions  = array();
			
			$sql = "SELECT entities.unique_key, entities.main, questions.type, entities.weight FROM entities ";
			$sql.= "LEFT JOIN questions on entities.guid = questions.guid ";
			$sql.= "where entities.father_guid = $quiz_guid order by entities.weight ASC ";
			
			$result = $this->get_records_with_sql($sql);
			
			if(count($result) > 0)
			{
				$count = 0;
				foreach($result AS $row)
				{
					$questions[$count]['unique_key'] 	= $row['unique_key'];
					$questions[$count]['main']			= $row['main'];
					$questions[$count]['type']			= $row['type'];
					$questions[$count]['weight']		= $row['weight'];
					$count++;
				}
			}
			
			return $questions;
		}
		
		private function create_a_question($course_guid, $section_guid, $node_guid, $quiz_guid, $main, $weight, $type = 'option')
		{
			$this->ci->db->trans_start();
			
			//首先，创建一个“entity”
			$question_unique_key = $this->generate_entity_unique_key(rand_str(32));
			$guid = $this->create_an_entity($this->_guid, 'question', $quiz_guid, TRUE, $question_unique_key, '', $main, $weight);
			
			//在“questions”表中创建记录
			
			$data = array(
				'guid' 			=> $guid,
				'unique_key'	=> $question_unique_key,
				'type'			=> strtoupper($type),
				'quiz_guid'		=> $quiz_guid,
				'node_guid' 	=> $node_guid,
				'section_guid' 	=> $section_guid,
				'course_guid'	=> $course_guid
			);
			
			$this->create_a_record('questions', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			//如果存储事件成功，把刚刚创建的“guid”赋值到“quiz_guid”上面
			if ($this->ci->db->trans_status() === TRUE)
			{
				return array('question_guid' => $guid, 'question_unique_key' => $question_unique_key);
			}
			
			return array();
		}
		
		//创建一个完型填空题
		function add_a_question_with_gaps_and_options($course_unique_key, $section_unique_key, $node_unique_key, $question_main)
		{
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$course_guid 	= $this->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->get_guid_by_unique_key($node_unique_key);
			$quiz_guid		= $this->get_guid_by_unique_key($quiz_unique_key);
			
			//创建一个问题
			$question = $this->create_a_question($course_guid, $section_guid, $node_guid, $quiz_guid, $question_main, 'gap_and_options');
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			//如果存储事件成功，把刚刚创建的“guid”赋值到“quiz_guid”上面
			if ($this->ci->db->trans_status() === TRUE)
				return array('question_unique_key' => $question['question_unique_key'], 'question_guid' => $question_guid);
			
			return array();
		}
		
		//创建一个填空题
		function add_a_question_with_gaps($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $main, $gaps, $weight)
		{
			if(count($gaps) == 0)
			{
				$this->set_a_msg('填空数据不能为空', 'error');
				return array();
			}
			
			foreach($gaps AS $row)
			{
				if($row['answer'] == '')
				{
					$this->set_a_msg('有一个空没指定答案', 'error');
					return array();
				}
			}
			
		
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$course_guid 	= $this->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->get_guid_by_unique_key($node_unique_key);
			$quiz_guid		= $this->get_guid_by_unique_key($quiz_unique_key);
			
			//创建一个问题
			$question = $this->create_a_question($course_guid, $section_guid, $node_guid, $quiz_guid, $main, $weight, 'gap');
			
			foreach($gaps AS $row)
			{
				$data = array(
					'key'			=> $row['key'],
					'question_guid' => $question['question_guid'],
					'answer'		=> $row['answer'],
					'explanation'	=> $row['explanation']
				);
				
				$this->create_a_record('gaps', $data);
			}
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			//如果存储事件成功，把刚刚创建的“guid”赋值到“quiz_guid”上面
			if ($this->ci->db->trans_status() === TRUE)
			{
				$data = array('question_unique_key' => $question['question_unique_key']);
				return $data;
			}
			
			return array();
		}
		
		//获取一个问题以及问题相关的全部备选答案信息，组合成一个数组，回传
		function get_a_question_with_multiple_options($question_unique_key)
		{
			$question_guid = $this->get_guid_by_unique_key($question_unique_key);
			return $this->get_multiple_options($question_guid);
		}
		
		//获取一个问题的备选答案
		function get_multiple_options($question_guid)
		{
			$options = array();
			
			//获取一个问题的全部选项
			$fields = array('*');
			$condition = array('question_guid' => $question_guid);
			$result = $this->get_records('options', $fields, $condition, NULL, 0, 'weight', 'ASC');
			
			$count = 0;
			
			foreach($result AS $row)
			{
				$options[$count]['id']			= $row['id'];
				$options[$count]['text'] 		= $row['text'];
				$options[$count]['is_correct'] 	= $row['is_correct'];
				$options[$count]['explanation']	= $row['explanation'];
				$options[$count]['weight']		= $row['weight'];
				$count++;
			}
			
			return $options;
		}
		
		//获取一道填空题
		function get_a_question_with_gaps($question_unique_key)
		{
			$question_guid = $this->get_guid_by_unique_key($question_unique_key);
			return $this->get_gaps($question_guid);
		}
		
		function get_gaps($question_guid)
		{
			$gaps = array();
			$fields = array('*');
			$condition = array('question_guid' => $question_guid);
			$result = $this->get_records('gaps', $fields, $condition);
			
			$count = 0;
			
			foreach($result AS $row)
			{
				$gaps[$count]['key']			= $row['key'];
				$gaps[$count]['answer']			= $row['answer'];
				$gaps[$count]['explanation']	= $row['explanation'];
				
				$count++;
			}
			
			return $gaps;
		}
		
		//创建一个选择问题
		function add_a_question_with_options($course_unique_key, $section_unique_key, $node_unique_key, $quiz_unique_key, $main, $weight, $options)
		{
		
			//check “options”
			if(gettype($options) != 'array')
			{
				$this->set_a_msg('备选答案的格式有误', 'error');
				return array();
			}
			elseif(count($options) < 2)
			{
				$this->set_a_msg('至少提供两个备选答案', 'error');
				return array();
			}
			else
			{
				$correct_sum = 0;
			
				for($i = 1; $i <= count($options); $i++)
				{	
					if(!array_key_exists('option_'.$i, $options))
					{
						$this->set_a_msg('备选项设置有误', 'error');
						return array();
					}
					
					if(!array_key_exists('text', $options['option_'.$i]) OR $options['option_'.$i]['text'] == '')
					{
						$this->set_a_msg('备选项设置有误或赋值为空 - text', 'error');
						return array();
					}
					
					if(!array_key_exists('is_correct', $options['option_'.$i]) OR !isset($options['option_'.$i]['is_correct']))
					{
						$this->set_a_msg('备选项设置有误或赋值为空 - is_correct', 'error');
						return array();
					}
					
					if(!array_key_exists('explanation', $options['option_'.$i]))
					{
						$this->set_a_msg('备选项设置有误 - explanation', 'error');
						return array();
					}
					
					$correct_sum = (int)$correct_sum + (int)$options['option_'.$i]['is_correct'];
				}
				
				if($correct_sum == 0)
				{
					$this->set_a_msg('至少有一个备选答案要设置为正确', 'error');
					return array();
				}
			}
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$course_guid 	= $this->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->get_guid_by_unique_key($node_unique_key);
			$quiz_guid		= $this->get_guid_by_unique_key($quiz_unique_key);
			
			//创建一个问题
			$question = $this->create_a_question($course_guid, $section_guid, $node_guid, $quiz_guid, $main, $weight);
			
			//在“options”表中创建答案
			
			$option_result_array = array();
			$count = 1;
			$weight = 50000;
			foreach($options AS $row)
			{
				$data = array(
					'question_guid'	=> $question['question_guid'],
					'text'			=> $options['option_'.$count]['text'],
					'is_correct'	=> $options['option_'.$count]['is_correct'],
					'explanation'	=> $options['option_'.$count]['explanation'],
					'weight'		=> $weight,
				);
				
				$option_id = $this->create_a_record('options', $data);
				$option_result_array['option_'.$count] 	= $option_id;
				$count++;
				$weight = $weight + 50000;
			}
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			//如果存储事件成功，把刚刚创建的“guid”赋值到“quiz_guid”上面
			if ($this->ci->db->trans_status() === TRUE)
			{
				return array(
					'question_guid'			=> $question['question_guid'],
					'question_unique_key' 	=> $question['question_unique_key'], 
					'options' 				=> $option_result_array
				);
			}
			
			return array();
		}
		
		//创建一个测试
		function add_a_quiz($course_unique_key, $section_unique_key, $node_unique_key, $title, $main = '', $weight)
		{
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$course_guid 	= $this->get_guid_by_unique_key($course_unique_key);
			$section_guid	= $this->get_guid_by_unique_key($section_unique_key);
			$node_guid		= $this->get_guid_by_unique_key($node_unique_key);
			
			//首先，创建一个“entity”
			$quiz_unique_key = $this->generate_entity_unique_key(rand_str(32));
			$guid = $this->create_an_entity($this->_guid, 'quiz', $node_guid, TRUE, $quiz_unique_key, $title, $main, $weight);
			
			//在“quiz”表中创建记录
			
			$data = array(
				'guid' 			=> $guid,
				'unique_key'	=> $quiz_unique_key,
				'node_guid' 	=> $node_guid,
				'section_guid' 	=> $section_guid,
				'course_guid'	=> $course_guid
			);
			
			$this->create_a_record('quizzes', $data);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			//如果存储事件成功，把刚刚创建的“guid”赋值到“quiz_guid”上面
			if ($this->ci->db->trans_status() === TRUE) return $quiz_unique_key;
			
			return NULL;
		}
		
		//更新一个测试
		function update_a_quiz($node_unique_key, $data)
		{
			$condition = array('unique_key' => $node_unique_key);
			return $this->update_records('entities', $data, $condition);
		}
		
		//更新一个选择题
		function update_a_question_with_options($question_unique_key, $data)
		{
			$returned_data = array();
				
			//验证$data数组
		
			//设置更新条件
			$condition = array('unique_key' => $question_unique_key);
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			//首先，更新问题的“main”信息
			$question_main = filter_str($data['question_main']);
			$question_main_data = array('main' => filter_str($question_main));
			$this->update_records('entities', $question_main_data, $condition);
			
			//然后删除那些被删除的选项
			$options_deleted = $data['options']['deleted'];
			
			if($options_deleted != '')
			{
				$number_of_deleted = count($options_deleted);
				for($i = 0; $i< $number_of_deleted; $i++)
				{
					$condition = array('id' => $options_deleted[$i]);
				 	$this->delete_records('options', $condition);
				}
			}
			
			//然后更新那些进行更新的选线
			$options_updated = $data['options']['updated'];
			if($options_updated != '')
			{
				$keys = array_keys($options_updated);
				foreach($keys AS $key)
				{
					$condition = array('id' => (int)$key);
					
					$update_data = array(
						'text'			=> filter_str($options_updated[$key]['text']),
						'is_correct'	=> $options_updated[$key]['is_correct'],
						'explanation'	=> filter_str($options_updated[$key]['explanation'])
					);
					
					$this->update_records('options', $update_data, $condition);
				}
			}
			
			//最后，添加那些新添加的选项
			$options_added = $data['options']['added'];
			if($options_added != '')
			{
				$question_guid 			= $this->get_guid_by_unique_key($question_unique_key);
				$option_result_array 	= array();
				$count 					= 1;
				
				foreach($options_added AS $row)
				{
					$data = array(
						'question_guid'	=> $question_guid,
						'text'			=> filter_str($options_added['option_'.$count]['text']),
						'is_correct'	=> $options_added['option_'.$count]['is_correct'],
						'explanation'	=> filter_str($options_added['option_'.$count]['explanation']),
						'weight'		=> $options_added['option_'.$count]['weight']
					);
				
					$option_id = $this->create_a_record('options', $data);
					$option_result_array['option_'.$count] 	= $option_id;
					$count++;
				}
				
				$returned_data['options_added'] = $option_result_array;
			}
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			$result = $this->ci->db->trans_status();
			
			return array('result' => $result, 'data' => $returned_data);
		}
		
		
		
		//更新一个填空题
		function update_a_question_with_gaps($question_unique_key, $data)
		{		
			//print_r($data);
		
			//验证$data数组
			
			if(count($data) == 0)
			{
				$this->set_a_msg('填空数据不能为空', 'error');
				return array();
			}
			
			/**
			if(count($data['gaps']['updated']) > 0)
			{
				foreach($data['gaps']['updated'] AS $row)
				{
					
				}
			}
			*/
		
			//设置更新条件
			$condition = array('unique_key' => $question_unique_key);
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			//首先，更新问题的“main”信息
			$question_main = filter_str($data['question_main'], '<img>');
			$main_data = array('main' => $question_main);
			$this->update_records('entities', $main_data, $condition);
			
			//然后删除那些被删除的选项
			$gaps_deleted = $data['gaps']['deleted'];
			
			if(gettype($gaps_deleted) == 'array')
			{
				$number_of_deleted = count($gaps_deleted);
				for($i = 0; $i< $number_of_deleted; $i++)
				{
					$condition = array('key' => $gaps_deleted[$i]);
				 	$this->delete_records('gaps', $condition);
				}
			}
			
			//然后更新那些进行更新的选线
			$gaps_updated = $data['gaps']['updated'];
			
			//如果问题的数组中包含了更新数据
			if(gettype($gaps_updated) == 'array')
			{
				foreach($gaps_updated AS $row)
				{
					$condition = array('key' => $row['key']);
					$update_data = array('answer' => filter_str($row['answer']), 'explanation' => filter_str($row['explanation']));
					$this->update_records('gaps', $update_data, $condition);
				}
			}
			
			//最后，添加那些新添加的选项
			$gaps_added = $data['gaps']['added'];
			
			if(gettype($gaps_added) == 'array')
			{
				$question_guid = $this->get_guid_by_unique_key($question_unique_key);
				foreach($gaps_added AS $row)
				{
					$data = array(
						'question_guid'	=> $question_guid,
						'key'			=> $row['key'],
						'answer'		=> filter_str($row['answer']),
						'explanation'	=> filter_str($row['explanation'])
					);
					
					$this->create_a_record('gaps', $data);
				}
			}
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			return $this->ci->db->trans_status();
		}
		
	}