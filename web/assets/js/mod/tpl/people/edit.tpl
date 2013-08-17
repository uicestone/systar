<div>
<div class="widget-box">
    <div class="widget-header">
        <h4>基本信息</h4>
    </div>

    <div class="widget-body">
        <div class="widget-main form-inline">
            <label for="name">姓名</label>
            <input type="text" id="name" value="<%=meta.name%>">

            <label for="phone">手机</label>
            <div class="input-prepend">
                <span class="add-on">
                    <i class="icon-phone"></i>
                </span>
                <input value="<%=getMeta("性别")%>" class="input-medium input-mask-phone" type="text" id="phone" >
            </div>


            <label for="birthday">生日</label>
            <div class="input-append">
                <input class="span10 date-picker" id="birthday" type="text" data-date-format="yyyy-mm-dd" value="">
                <span class="add-on">
                    <i class="icon-calendar"></i>
                </span>
            </div>
        </div>
    </div>
</div>


<div class="widget-box">
    <div class="widget-header">
        <h4>来源</h4>
    </div>

    <div class="widget-body">
        <div class="widget-main form-inline">
            <select name="profiles[来源类型]">
            <option value="" disabled="disabled">来源类型</option><optgroup label="所内案源"><option value="律所网站" selected="selected">律所网站</option><option value="其他网络">其他网络</option><option value="线下媒体">线下媒体</option><option value="律所营销活动">律所营销活动</option><option value="合作单位介绍">合作单位介绍</option><option value="陌生上门">陌生上门</option></optgroup><optgroup label="个人案源"><option value="亲友介绍">亲友介绍</option></optgroup><option value="老客户介绍">老客户介绍</option>
            </select>

            <input type="text" class"span10" name="people[staff_name]" placeholder="来源律师" value="<%=getMeta("来源律师")%>" data-model="">
        </div>
    </div>
</div>




<div class="widget-box" data-widget-type="table" data-removable="true" data-per="5" data-order-by="id" data-order="asc" data-args="{order_by:1,order:}">
    <div class="widget-header">
        <h4>资料项</h4>
    </div>

    <div class="widget-body">
        <div class="widget-main">
            <table>
                <tr>
                    <th data-cell="string" data-name="name" data-label="名称"></th>
                    <th data-cell="tags" data-name="content" data-label="内容"></th>
                </tr>
            </table>
        </div>
    </div>
</div>


</div>