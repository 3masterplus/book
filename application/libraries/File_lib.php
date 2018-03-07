<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class File_lib extends my_lib
	{
		var $_upload_base_path;
		var $_file_allowed_types;
		var $_file_max_size;
		var $_image_allowed_types;
		var $_image_max_size;
		var $_image_max_width;
		var $_image_max_height;
	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
					
			$this->_upload_base_path 	= config('upload_base_path');
			$this->_file_allowed_type	= config('file_allowed_types');
			$this->_file_max_size		= config('file_max_size');
			
			$this->_image_allowed_types	= config('image_allowed_types');
			$this->_image_max_size		= config('image_max_size');
			$this->_image_max_width		= config('image_max_width');
			$this->_image_max_height	= config('image_max_height');		
		}
		
		function delete_an_image($image_id, $image_path)
		{
			$condition = array('id' => $image_id);
			
			//开始数据库存储事件
			$this->ci->db->trans_start();
			
			$this->delete_records('images', $condition);
			$this->delete_records('image_entity_relations', $condition);
			
			//结束数据库存储事件
			$this->ci->db->trans_complete();
			
			if ($this->ci->db->trans_status() === TRUE)
			{
				unlink($image_path);
				return TRUE;
			}
			
			return FALSE;
		}
		
		
		//改变一个图片的尺寸
		function resize_an_image($image_path, $width, $height, $cache_dir, $filename)
		{
			$this->ci->load->library('ce_image'); //加载“ce_image”库文件
			
			//initialize ce_image class
			$ce_image = new Ce_image();
			
			
			$ce_image->make($image_path, array(
				'max'					=> $width,
				'allow_scale_larger'	=> TRUE,
				'width' 				=> $width,
				'height' 				=> $height,
				'cache_dir'				=> $cache_dir,
				'hide_relative_path' 	=> TRUE,
				'filename'				=> $filename,
				'unique'				=> 'none',
				'quality'				=> 100,
				'crop'					=> FALSE
				)
			);
			
			$filename = $ce_image->get_filename().'.'.$ce_image->get_extension();
			
			$ce_image->close();
			
			return $cache_dir.$filename;
		}
		
		/*
			传入图片的地址和相关ENTITY的ID, 
			存入图片并建立图片和ENTITY之间的关联
		*/
		function create_image_attachment($image_data, $related_guid, $relation = 'attachment')
		{
			$id = $this->save_to_db($image_data);
			$relation = strtoupper($relation);
			$this->relate_to_entity($related_guid, $id, $relation);
			return $id;
		}
		
		
		//剪切图片
		//$type类型包括：“crop_an_avatar”，“crop_a_course_banner”
		function crop_an_image($image_path, $x1, $y1, $x2, $y2, $ratio, $unique_key, $type = 'crop_an_avatar')
		{
			//加载“ce_image”库文件
			$this->ci->load->library('ce_image');
			
			//initialize ce_image class
			$ce_image = new Ce_image();
			
			if($type == 'crop_an_avatar')
			{
				$upload_path = $this->generate_avatar_path($unique_key);
			}
			elseif($type == 'crop_a_course_banner')
			{
				$upload_path = $this->_upload_base_path.$this->_user_unique_key.'/images/course/'.$unique_key.'/';
			}
			
			//peform image crop action
			$ce_image->make($image_path, array(
					'width' 				=> $width = ($x2-$x1)*$ratio,
					'height' 				=> $height = ($y2-$y1)*$ratio,
					'cache_dir'				=> $upload_path,
					'hide_relative_path' 	=> TRUE,
					'filename' 				=> $unique_key.'_'.$width.'_'.$height,
					'unique'				=> 'none',
					'quality' 				=> 100,
					'crop' 					=> array(TRUE,array('left','top'),array($x1*$ratio, $y1*$ratio),FALSE)
				)
			);
			
			$file_ext = $ce_image->get_extension();
			$filename = $unique_key.'_'.$width.'_'.$height.'.'.$file_ext;
			$ce_image->close();
			
			$cropped_image_path	= $upload_path.'/'.$filename;
			
			if($type == 'crop_an_avatar')
			{
				$cropped_image_filename = $unique_key.'_200_200';
				$resized_image = $this->resize_an_image($cropped_image_path, 200, 200, $upload_path, $cropped_image_filename);
			}
			elseif($type == 'crop_a_course_banner')
			{
				$cropped_image_filename = $unique_key.'_400_300';
				$resized_image = $this->resize_an_image($cropped_image_path, 400, 300, $upload_path, $cropped_image_filename);
			}
			
			//删除临时生成的图片
			unlink($cropped_image_path);
			
			if($type == 'crop_an_avatar')
			{
				//更新用户表中的“avatar_url”的数据
				$this->update_records('users', array('avatar_url' => $resized_image), array('unique_key' => $unique_key));
			
				//更新“session”中的“avatar_url”的数据
				$this->ci->session->set_userdata(array('avatar_url' => $resized_image));
			}
			elseif($type == 'crop_a_course_banner')
			{
				//更新用户表中的“avatar_url”的数据
				$this->update_records('courses', array('banner_url' => $resized_image), array('unique_key' => $unique_key));
			}
			
			
			return array('filename' => $cropped_image_filename.'.'.$file_ext, 'upload_path' => $upload_path);
		}
		
		//上传图片
		function upload_an_image($user_unique_key, $entity_guid, $relation = 'attachment', $original_path = '', $title = '', $description = '', $if_full_path_returned = FALSE)
		{
			//加载文件上传资源
			$this->ci->load->library('upload');
		
			$data_returned = array();
			$type = 'images';
			
			$user_unique_key = iif($user_unique_key != '', $user_unique_key, 'public');
			
			$setting = array(
				'allowed_types'	=> $this->_image_allowed_types,
				'max_size'		=> $this->_image_max_size,
				'max_width'		=> $this->_image_max_width,
				'max_height'	=> $this->_image_max_height,
				'upload_path'	=> $this->generate_file_path($user_unique_key, $type),
				'file_name'		=> $this->generate_file_name()
			);
			
			$this->ci->upload->initialize($setting);
			
			if(!$this->ci->upload->do_upload())
			{
				$msg = strip_tags($this->ci->upload->display_errors());
				$this->set_a_msg($msg, 'error');
			}
			else
			{
				$image_data = $this->ci->upload->data();
				$data = array(
					'filename'		=> $image_data['file_name'],
					'extension'		=> $image_data['file_ext'],
					'width'			=> $image_data['image_width'],
					'height'		=> $image_data['image_height'],
					'size'			=> $image_data['file_size'],
					'full_path'		=> $image_data['full_path'],
					'upload_path'	=> $setting['upload_path'],
					'title'			=> $title,
					'description'	=> $description,
					'original_name'	=> $image_data['client_name'],
					'original_path'	=> $original_path
				);
				
				$id = $this->save_to_db($data, $type);
				
				$relation = strtoupper($relation);
				$weight = 0;
				
				$this->relate_to_entity($entity_guid, $id, $relation, $weight, $type);
				
				//拼接返回的数据
				$data_returned = array(
					'filename'		=> $data['filename'],
					'upload_path' 	=> $data['upload_path'],
					'width'			=> $data['width'],
					'height' 		=> $data['height'],
					'image_id'		=> $id
				);
				
				if($if_full_path_returned) $data_returned['full_path'] = $data['full_path'];
			}
			
			return $data_returned;
		}
		
		//上传文件
		function upload_a_file($user_unique_key, $entity_guid, $relation = 'attachment', $allowed_types = '', $title = '', $description = '')
		{
			//加载文件上传资源
			$this->ci->load->library('upload');
		
			$data = array();
			$type = 'files';
			
			if($allowed_types == '')
			{
				$allowed_types = $this->_file_allowed_type;
			}
			
			$user_unique_key = iif($user_unique_key == '', 'public', $this->_user_unique_key);
			
			$setting = array(
				'allowed_types'	=> $allowed_types,
				'max_size'		=> $this->_file_max_size,
				'upload_path'	=> $this->generate_file_path($user_unique_key, $type),
				'file_name'		=> $this->generate_file_name()
			);
			
			$this->ci->upload->initialize($setting);
			
			if(!$this->ci->upload->do_upload())
			{
				$msg = strip_tags($this->ci->upload->display_errors());
				$this->set_a_msg($msg, 'error');
			}
			else
			{
				$file_data = $this->ci->upload->data();
				
				$data = array(
					'filename'		=> $file_data['file_name'],
					'extension'		=> $file_data['file_ext'],
					'size'			=> $file_data['file_size'],
					'full_path'		=> $file_data['full_path'],
					'upload_path'	=> $setting['upload_path'],
					'title'			=> $title,
					'description'	=> $description,
					'original_name'	=> $file_data['client_name']
				);
				
				$id = $this->save_to_db($data, $type);
				
				$relation 	= strtoupper($relation);
				$weight 	= 0;
				
				$this->relate_to_entity($entity_guid, $id, $relation, $weight, $type);
				
				$data = array(
					'id'			=> $id,
					'filename' 		=> $data['filename'],
					'upload_path'	=> $data['upload_path'],
					'original_name'	=> $data['original_name']
				);
			}
			
			return $data;
		}
		
		//将图片保存到磁盘上
		private function generate_file_path($user_unique_key, $type = 'images')
		{
			//计算用户上传图片的文件夹
			$file_path = $this->_upload_base_path.$user_unique_key.'/'.$type;
			
			//创建用户文件夹
			if(!file_exists($file_path)) mkdir($file_path, 0777, TRUE);
			
			//计算文件存储文件夹
			$file_path = $file_path.'/'.date('Y', time()).'/'.date('m', time()).'/'.date('d', time()).'/';
				
			//创建文件存储文件夹
			if(!file_exists($file_path)) mkdir($file_path, 0777, TRUE);
			
			return $file_path;
		}
		
		private function generate_avatar_path($user_unique_key)
		{
			$file_path = $this->_upload_base_path.$user_unique_key.'/images/avatar/';
			return $file_path;
		}
		
		//生成文件名
		private function generate_file_name()
		{
			return time().rand_str(6, '1234567890');
		}
		
		//把上传成功的文件信息保持到数据库中
		private function save_to_db($data, $type = 'images')
		{
			$data['user_guid'] = $this->_guid;
			return $this->create_a_record($type, $data);
		}
		
		/**
			建立“Entity GUID”和“file id”之间的关联
			“relation”当前仅包括：“attachement”
		*/
		private function relate_to_entity($entity_guid, $id, $relation = 'attachment', $weight = 0, $type = 'images')
		{
			$data = array('guid' => $entity_guid, 'id' => $id, 'weight' => $weight, 'relation' => $relation);
		
			if($type == 'images') $this->create_a_record('image_entity_relations', $data);
			elseif($type == 'files') $this->create_a_record('file_entity_relations', $data);
			
			return TRUE;
		}
		
	}