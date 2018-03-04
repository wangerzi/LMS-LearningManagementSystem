var app = new Vue({
    el:		'#addPlan',
    data: 	data,
    //渲染完成回调
    mounted: function () {
        // With JQuery
        $(".slider").slider();// 刷新
    },
    methods: {
        add_mission: function (e) {
//                    console.log(this.missions);
            this.missions.push({name: '', info: '', child: []});
        },
        del_mission: function (index) {
            wq_confirm('真的要删除这个任务吗？',$.proxy(function(){
                this.missions.splice(index,1);
            }, this));
        },
        del_child: function (index, i) {
            wq_confirm('真的要删除这个子任务吗？',$.proxy(function(){
                this.missions[index].child.splice(i,1);
            }, this));
        },
        slide_change: function (e) {
            this.alloc = this.slide_sum();
            console.log(this.alloc);
        },
        //获取当前滑块的总时间
        slide_sum: function () {
            var sum = 0;
            $('.slider').each(function () {
                if(this.value){
                    sum += parseFloat(this.value);
                }
            });
            return sum;
        },
        //这里可以进行效率优化
        end_to_start: function () {
            var start = Date.parse(new Date(this.start)) / 1000;
            var end = Date.parse(new Date(this.end)) / 1000;

            return (end - start) / 86400;
        },
        last_time: function () {
            return this.end_to_start() - this.alloc;
        }
    },
});
//调用datepicker插件
$('.input-daterange').datepicker({
    format: "yyyy-mm-dd",
    language:'zh-CN',
    //autoclose: true,
    startDate: today,
    todayHighlight: true
});