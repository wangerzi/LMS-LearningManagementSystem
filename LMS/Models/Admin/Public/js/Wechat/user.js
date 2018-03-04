/*
* @Author: 94468
* @Date:   2017-10-23 16:04:39
* @Last Modified by:   94468
* @Last Modified time: 2017-10-23 19:28:43
*/
function addBlackList(dom){
	var origin = $(dom);
	$('#addBlackList').modal('open');
}
function removeBlackList(dom){
	var origin = $(dom);
	$('#removeBlackList').modal('open');
}
function editRemark(dom){
	var origin = $(dom);
	var modal = $('#editRemark');
	modal.modal('open');

	modal.find('input[name="remark"]').val(origin.attr('data-remark'));
	modal.find('input[name="openid"]').val(origin.attr('data-openid'));
}
/**
 * 单选一个
 * @param  {[type]} dom [description]
 * @return {[type]}     [description]
 */
function chooseOne(dom){
	var origin = $(dom);
	var tr = origin.parents('tr');
	var form = origin.parents('form');

	// 先将form中的所有选项闭合，然后，选中tr中的input[type="checkbox"]
	form.find('input[name="openid[]"]').each(function(i){
		if(this.checked)
			$(this).next().click();
	});
	// 单选自己
	tr.find('input[name="openid[]"]').next().click();
}
/**
 * 全选
 * @param  {[type]} dom [description]
 * @return {[type]}     [description]
 */
function selectAll(dom){
	var origin = $(dom);
	var form = origin.parents('form');
	var openids = form.find('input[name="openid[]"]');

	if(origin.prev().is(':checked'))
		return ;

	openids.each(function(i, e){
		if(this.checked)
			return true;
		$(this).next().click();
	});;
	// 单选自己
	tr.find('input[name="openid[]"]').next().click();
}