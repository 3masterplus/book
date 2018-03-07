<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class File extends MY_Controller
	{
		function __construct()
		{	
			parent::__construct();
			$this->load->library('file_lib');
			$this->load->library('form_validation');
		}
		
		function ajax_crop_an_image()
		{
			$this->members_only();
			if(CI_POST('crop_an_image'))
			{
				$image_path = CI_POST('image_path');
				$x1			= CI_POST('x1');
				$y1			= CI_POST('y1');
				$x2			= CI_POST('x2');
				$y2			= CI_POST('y2');
				$ratio		= CI_POST('ratio');
				$type		= CI_POST('type');
				$unique_key	= CI_POST('unique_key');
				
				
				$this->form_validation->set_rules('image_path', '图片地址', 'required');
				$this->form_validation->set_rules('x1', '坐标x1', 'required');
				$this->form_validation->set_rules('y1', '坐标y1', 'required');
				$this->form_validation->set_rules('x1', '坐标x2', 'required');
				$this->form_validation->set_rules('y1', '坐标y2', 'required');
				$this->form_validation->set_rules('ratio', '缩放比例', 'required');
				
				if($this->form_validation->run())
				{
					//剪切图片
					$cropped_array = $this->file_lib->crop_an_image($image_path, $x1, $y1, $x2, $y2, $ratio, $unique_key, $type);
					
					if(count($cropped_array) > 0)
					{
						$this->ajax_ini($cropped_array);
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
		
		//获取一个mp3文件的波形图
		function ajax_get_mp3_wave()
		{
			if(CI_POST('get_mp3_wave'))
			{
				$mp3_file_path = CI_POST('mp3_file_path');
				$absolute_mp3_file_path = FCPATH.ltrim($mp3_file_path, '/');
				
				if(!file_exists($absolute_mp3_file_path))
				{
					$this->_ajax_message = array('文件目录不存在！', 'error');
				}
				else
				{
					$pathinfo = pathinfo($mp3_file_path);
					
					$file_extenstion = strtoupper($pathinfo['extension']);
					
					if($file_extenstion != 'MP3')
					{
						$this->_ajax_message = array('文件类型不支持', 'error');
					}
					else
					{
						$file_basename	= strtoupper(basename($mp3_file_path));//获取文件名
						$file_basename	= basename($file_basename, '.MP3');
						
						$file_path = $pathinfo['dirname'];
						
						$absolute_mp3_wave_json_file_path = FCPATH.ltrim($file_path, '/').'/'.$file_basename.'.json';
						
						if(!file_exists($absolute_mp3_wave_json_file_path))
						{
							generate_wav($absolute_mp3_file_path);
						}
						
						$relative_mp3_wave_json_file_path = $file_path.'/'.$file_basename.'.json';
						
						$this->ajax_ini($relative_mp3_wave_json_file_path);
					}
				}
				
				$this->ajax_response();
			}
		}
		
		
		//图片上传
		function ajax_upload_an_image()
		{
			$this->members_only();
			if(CI_POST('upload_an_image'))
			{
				//获取当前操作用户的GUID
				$user_guid = $this->_guid;
				
				//获取从表单通过“POST”方法传过来的数据
				$entity_unique_key	= CI_POST('entity_unique_key');
				$relation			= (CI_POST('relation')) ? CI_POST('relation') : strtoupper('attachment');
				$title				= (CI_POST('title')) ? CI_POST('title') : '';
				$original_path		= (CI_POST('original_path')) ? CI_POST('original_path') : '';
				$description		= (CI_POST('description')) ? CI_POST('description') : '';
				
				//验证“entity_unique_key”
				$this->form_validation->set_rules('entity_unique_key', 'ENTITY_UNIQUE_KEY', 'required|callback_is_existent[entities.unique_key]');
				
				if($this->form_validation->run())
				{
					//$this->gate_for_ajax($entity_unique_key, $this->_guid);
					
					$entity_guid		= $this->my_lib->get_guid_by_unique_key($entity_unique_key);
					$user_unique_key	= $this->_user_unique_key;
					
					//开始上传
					$upload = $this->file_lib->upload_an_image($user_unique_key, $entity_guid, $relation, $original_path, $title, $description);
					
					if(count($upload) > 0) $this->ajax_ini($upload);
					else $this->_ajax_message = $this->my_lib->generate_error_message();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();	
			}
		}
		
		//删除图片
		function ajax_delete_an_image()
		{
			$this->members_only();
			if(CI_POST('delete_an_image'))
			{
				$image_id 				= CI_POST('image_id');
				$image_path				= CI_POST('image_path');
				$original_image_id 		= CI_POST('original_image_id');
				$original_image_path	= CI_POST('original_image_path');
				
				$this->form_validation->set_rules('image_id', '图片ID', 'required');
				$this->form_validation->set_rules('image_path', '图片路径', 'required|callback_file_path_check');
				$this->form_validation->set_rules('original_image_id', '原图ID', 'required');
				$this->form_validation->set_rules('original_image_path', '原图路径', 'required|callback_file_path_check');
				
				if($this->form_validation->run())
				{
					//开始数据库存储事件
					$this->db->trans_start();
				
					$this->file_lib->delete_an_image($image_id, $image_path);
					$this->file_lib->delete_an_image($original_image_id, $original_image_path);
					
					//结束数据库存储事件
					$this->db->trans_complete();
			
					if ($this->db->trans_status() === TRUE) $this->ajax_ini();
					else $this->_ajax_message = '图片删除失败';
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
			
			return NULL;
		}
		
		//更新一个图片的描述信息
		function ajax_update_an_image_caption()
		{
			$this->members_only();
			if(CI_POST('update_an_image_caption'))
			{
				$id = CI_POST('image_id');
				
				//验证“ID”
				$this->form_validation->set_rules('image_id', '图片ID', 'required|callback_is_existent[images.id]');
				
				if($this->form_validation->run())
				{
					$condition 	= array('id' => $id);
					$caption = $this->my_lib->get_a_value('images', 'description', $condition);
					$data = array('description' => CI_POST('image_caption'));
					$this->my_lib->update_records('images', $data, $condition);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//更新一个文件的文件名
		function ajax_update_a_file_title()
		{
			$this->members_only();
			if(CI_POST('update_a_file_title'))
			{
				$file_id 	= CI_POST('file_id');
				$file_title = CI_POST('file_title');
				$duration	= CI_POST('duration') ? CI_POST('duration') : null;
				
				//验证“ID”
				$this->form_validation->set_rules('file_id', 'file_id', 'required|callback_is_existent[files.id]');
				
				if($this->form_validation->run())
				{
					$condition 	= array('id' => $file_id);
					$data = array('title' => $file_title);
					
					if($duration != null) $data['duration'] = $duration;
					
					$this->my_lib->update_records('files', $data, $condition);
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//上传一张图片
		function ajax_insert_an_image()
		{
			$this->members_only();
			if(CI_POST('insert_an_image'))
			{
				$subtype = CI_POST('subtype');
				$entity_unique_key = CI_POST('entity_unique_key');
				
				$this->form_validation->set_rules('subtype', 'subtype', 'required');
				$this->form_validation->set_rules('entity_unique_key', 'entity_unique_key', 'required');
				
				if($subtype == 'node')
				{
					$this->form_validation->set_rules('entity_unique_key', 'NODE_UNIQUE_KEY', 'callback_is_existent[nodes.unique_key]');
					$node_guid = $this->my_lib->get_guid_by_unique_key($entity_unique_key);
					$entity_guid = $node_guid;
				}
				elseif($subtype == 'section')
				{
					$this->form_validation->set_rules('entity_unique_key', 'SECTION_UNIQUE_KEY', 'callback_is_existent[sections.unique_key]');
					$section_guid = $this->my_lib->get_guid_by_unique_key($entity_unique_key);
					$entity_guid = $section_guid;
				}
				
				if($this->form_validation->run())
				{
					$this->gate_for_ajax($entity_unique_key, $this->_guid);
				
					$user_unique_key = $this->_user_unique_key;
					
					//开始上传
					$upload = $this->file_lib->upload_an_image($user_unique_key, $entity_guid, 'attachment', '', '', '', TRUE);
					
					if(count($upload) == 0)
					{
						$this->_ajax_message = $this->my_lib->generate_error_message();
					}
					else
					{
						$image_path = $upload['upload_path'].$upload['filename'];
						$width 		= CI_POST('width');
						$cache_dir	= $upload['upload_path'];
						
						$filename_array = explode('.', $upload['filename']);
						$filename		= $filename_array[0].'_'.$width;
						
						//计算裁剪出来的图片宽度
						if($upload['width'] < $width)
						{
							$width = $upload['width'];
						}
						
						//剪切出新的图片
						$resized_image = $this->file_lib->resize_an_image($image_path, $width, '', $cache_dir, $filename);
						
						//当图片生成成功，将图片信息存入数据库并建立图片和相关ENTITY之间的关系
						if($resized_image != '')
						{
							$pathinfo 			= pathinfo($resized_image);
							$image_size_info	= getimagesize($resized_image);
							$filename 			= $pathinfo['basename'];
							$extension 			= '.'.$pathinfo['extension'];
							$width				= $image_size_info[0];
							$height				= $image_size_info[1];
							$size				= $image_size_info['bits'];
							$upload_path		= $pathinfo['dirname'];
							$full_path			= $upload['full_path'];
							$title				= (CI_POST('title')) ? CI_POST('title') : '';
							$description		= (CI_POST('description')) ? CI_POST('description') : '';
							$original_name		= $filename;
							$original_path		= $upload['upload_path'].$upload['filename'];
							
							$image_data = array(
								'user_guid'		=> $this->_guid,
								'filename'		=> $filename,
								'extension'		=> $extension,
								'width'			=> $width,
								'height'		=> $height,
								'size'			=> $size,
								'full_path'		=> $full_path,
								'upload_path'	=> $upload_path,
								'title'			=> $title,
								'description'	=> $description,
								'original_name'	=> $original_name,
								'original_path'	=> $original_path
							);
							
							$id = $this->file_lib->create_image_attachment($image_data, $entity_guid, 'sub_attachment');
						}
						
						$data = array(
							'upload_path' 					=> $upload_path,
							'filename'						=> $filename,
							'resized_image_id'				=> $id,
							'original_image_upload_path'	=> $upload['upload_path'],
							'original_image_filename'		=> $upload['filename'],
							'original_image_id'				=> $upload['image_id'],
							'url'							=> base_url(),
						);
						
						$this->ajax_ini($data);
						
						if($subtype == 'node')
						{
							$node = $this->my_lib->get_a_subtype_row('nodes', (int)$node_guid, array('section_guid', 'course_guid'));
							$section_guid = $node['section_guid'];
							$course_guid = $node['course_guid'];
							$this->count_lib->plus($node_guid, 'image');
						}
						elseif($subtype == 'section')
						{
							$section = $this->my_lib->get_a_subtype_row('sections', (int)$section_guid, array('course_guid'));
							$course_guid = $section['course_guid'];
						}
						
						//记录该节点、课节、以及课程插入了一张图片
						$this->count_lib->plus($section_guid, 'image');
						$this->count_lib->plus($course_guid, 'image');
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//文件上传
		function ajax_upload_a_file()
		{
			$this->members_only();
			if(CI_POST('upload_a_file'))
			{
				//获取当前操作用户的GUID
				$user_guid  = $this->_guid;
				
				//获取相关“ENTITY”的“UNIQUE_KEY”
				$entity_unique_key = CI_POST('entity_unique_key');
				
				$is_audio_file 	= (CI_POST('is_audio_file')) ? CI_POST('is_audio_file') : FALSE;
				$title			= (CI_POST('title')) ? CI_POST('title') : '';
				$description	= (CI_POST('description')) ? CI_POST('description') : '';
				
				$this->form_validation->set_rules('entity_unique_key', 'ENTITY_UNIQUE_KEY', 'required|callback_is_existent[entities.unique_key]');
				
				if($this->form_validation->run())
				{
					//$this->gate_for_ajax($entity_unique_key, $this->_guid);
				
					$entity_guid 		= $this->my_lib->get_guid_by_unique_key($entity_unique_key);
					$user_unique_key 	= $this->_user_unique_key;
					
					$allowed_types = iif($is_audio_file, 'mp3', '');
					$relation = iif($is_audio_file, 'audio attachment', '');
					
					
					//开始上传
					$upload = $this->file_lib->upload_a_file($user_unique_key, $entity_guid, $relation, $allowed_types, $title, $description);
					
					if(count($upload) > 0)
					{
						$entity_subtype_id = $this->my_lib->get_a_value('entities', 'subtype_id', array('guid' => $entity_guid));
						
						if($entity_subtype_id == $this->my_lib->get_subtype_id('node'))
						{
							$nodes = $this->my_lib->get_a_subtype_row('nodes', (int)$entity_guid, array('section_guid', 'course_guid'));
							$section_guid = $nodes['section_guid'];
							$course_guid = $nodes['course_guid'];
							
							//记录该节点、课节、以及课程插入了个MP3音频文件
							$this->count_lib->plus($entity_guid, 'mp3');
							$this->count_lib->plus($section_guid, 'mp3');
							$this->count_lib->plus($course_guid, 'mp3');
						}
					
						$this->ajax_ini($upload);
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
		
		//调用MY_FORM_VALIDATION的扩展方法，检查某个目录下的文件是否存在
		function file_path_check($file_path)
		{
			return $this->form_validation->file_path_check($file_path);
		}
	}