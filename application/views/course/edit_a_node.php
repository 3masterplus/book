<?php 
	if(!$is_pjax){
		echo '<div class="col-md-8 col-sm-8" id="detail">';
	}
?>
	<div class="form node-form bottom-space-4" data-key="<?php echo $node_unique_key; ?>">
		<h4>编辑：<?php echo $node['title']; ?></h4>
		<div class="form-body">
			<label>节点名称 <span>（必填）</span><i></i>
				<input type="text" name="node-name" value="<?php echo $node['title']; ?>">
			</label>
			<label>节点内容 <span>（必填）</span><i></i></label>
				
			<div id="postdivrich" class="postarea edit-form-section">
				<div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap tmce-active">
					<div id="wp-content-editor-tools" class="wp-editor-tools hide-if-no-js">
						<a id="content-html" class="wp-switch-editor switch-html">文本</a>
						<a id="content-tmce" class="wp-switch-editor switch-tmce">可视化</a>
					</div>
					
					<div id="wp-content-editor-container" class="wp-editor-container">
						<textarea name="node-introduction" class="meditor" id="content"><?php echo $node['main']; ?></textarea>
					</div>
				</div>
			</div>
			
			<label>
				<select name="status-select">
					<option value="published" <?php echo iif($node['status'] == 'PUBLISHED', 'selected = "selected"', ''); ?> >发布</option>
					<option value="draft" <?php echo iif($node['status'] == 'DRAFT', 'selected = "selected"', ''); ?> >草稿</option>
					<option value="closed" <?php echo iif($node['status'] == 'CLOSED', 'selected = "selected"', ''); ?> >关闭</option>
				</select>
			</label>
			
			<button class="btn btn-hilight" id="edit-a-node">提交</button>	
		</div>
	</div>
	
	<?php
		/** 
		if(count($quizzes) > 0){
			$this->load->view('course/quizzes', array('quizzes' => $quizzes));
		} else {
			echo '<a href="#" id="enable-quiz"><i class="icon-plus"> </i>添加测试</a>';
		}
		*/
	?>
	
<?php if(!$is_pjax){ ?></div><?php } ?>
<input type="hidden" id="course_unique_key" value="<?php echo $course_unique_key;?>">

<div class="insert-article upload" id="media" style="display:none;">
      <div class="uld_rt">
          <div class="uld_r_up">
              <div class="uld_header">
                  <h2>插入图片</h2>
                  <div class="close">&times;</div>
                  <p class="options"><a class="uld_anchor1 myanchor select" href="#">上传文件</a> <span>|</span> <a class="uld_anchor2 myanchor" href="#">媒体库</a> <span>|</span> <a class="uld_anchor3 myanchor" href="#" style="width: 100px;">从图片库选择</a></p>
              </div>

              <div class="uld_rup_file myitem">
                  <p class="p1">把文件拖到任何地方来长传</p>
                  <p class="or">或</p>
                  <span class="btn btn-action upload_btn fileinput-button">
                      <i class="glyphicon glyphicon-plus"></i>
                      <span>选择图片</span>
                      <!-- The file input field used as target for the file upload widget -->
                      <input id="fileupload" type="file" name="bt-image">
                  </span>
                  <p class="p2">最大上传文件大小：8MB</p>
              </div>
              <div class="uld_rup_media myitem" style="display: none;">
                 <div class="clearfix">
                      <div class="left">

                        <div class="table-pic">
                            <ul>
                                <?php if (@$post_images): foreach ($post_images as $k=>$image): ?>
                                    <?php
                                        $info = @$image['filename']."&&".@$image['width']."&&".@$image['height']."&&".@$image['filesize']."&&".@$image['guid']."&&".@$image['title']."&&".@$image['alt'];
                                     ?>
                                    <li><img id="pic_<?php echo @$image['guid'];?>" src="<?php echo @$image['link']; ?>" width="140px" height="140px"  data-info="<?php echo @$info;?> "><i class="cls">&minus;</i></li>
                                <?php endforeach; endif; ?>
                            </ul>
                        </div>
                      </div>
                    <div class="accessory"></div>
                  </div>
              </div>
              <div class="uld_rup_base myitem all-pic" style="display: none;">
                 <div class="clearfix">
                      <div class="left">
                        <div class="form-inline">
                            <div class="search-pic form-group">
                                <input class="form-control" type="text" placeholder="请输入图片描述或替代文本">
                                <button class="btn blue">搜索</button>
                            </div>
                        </div>
                        <div class="table-pic">
                            <ul>
                                
                            </ul>
                        </div>
                      </div>
                    <div class="accessory"></div>
                  </div>
              </div>
          </div>
          <div class="uld_r_bm">
              <div class="sel_msg">
                  <span class="sel_num"></span>
                  <span class="delete_all">清空</span>
              </div>
              <div class="sel_imgs"></div>
              <button class="btn insert-article-btn btn-disabled">保存</button>
          </div>
      </div>
</div>