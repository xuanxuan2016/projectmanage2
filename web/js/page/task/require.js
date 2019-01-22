(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var home = {
    };
}(Vue, window, document));
/**
 * vue对象
 */
var app = new Vue({
    el: '#app',
    data: {
        tableData: [{
                id: 1,
                date: '2016-05-02',
                name: '王小虎',
                address: '上海市普陀区金沙江路 1518 弄'
            }, {
                id: 2,
                date: '2016-05-04',
                name: '王小虎',
                address: '上海市普陀区金沙江路 1517 弄'
            }, {
                id: 3,
                date: '2016-05-01',
                name: '王小虎',
                address: '上海市普陀区金沙江路 1519 弄'
            }, {
                id: 4,
                date: '2016-05-03',
                name: '王小虎',
                address: '上海市普陀区金沙江路 1516 弄'
            }],
        checkListFenPei: {},
        checkListQa: {}
    },
    created: function() {
    },
    methods: {
        handleRowStyle: function(row, rowIndex) {
            console.log(row);
            if (row.row.date == '2016-05-03') {
                return {color: 'red'};
            }
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            console.log(app);
        });
    }
});
