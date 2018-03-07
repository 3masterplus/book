<div class="form" data-key="<?php echo $section['section_unique_key']; ?>"> 
  <h4><?php echo $section['title']; ?></h4>
  <div class="form-body"> 
    <label>
       
      课节名称 <span>（必填）  </span><i></i>
      <input type="text" name="section-name" value="<?php echo $section['title']; ?>">
    </label>
    <label>
       
      课节简介 <span>（必填）  </span><i></i>
      <textarea name="section-introduction"><?php echo $section['main']; ?> </textarea>
    </label>
    <label>
       
      价格<span>（必填）  </span><i></i>
      <input type="text" name="section-price" value="<?php echo $section['price']; ?>">
    </label>
    <button class="btn btn-hilight">提交 </button>
  </div>
</div>
 