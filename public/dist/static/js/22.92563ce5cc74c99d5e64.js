webpackJsonp([22],{HOob:function(e,t){},"dD+F":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var l=a("mvHQ"),r=a.n(l),n={data:function(){return{form:{name:"",linkman:"",phone:"",tax:"",dispatching:"",unloading:"",waiter:"",date:new Date},rules:{name:[{required:!0,trigger:"blur",message:"项目名称不能为空"}],linkman:[{required:!0,trigger:"blur",message:"联系人不能为空"}],phone:[{required:!0,trigger:"blur",message:"联系方式不能为空"}],tax:[{required:!0,trigger:"blur",message:"税率不能为空"}]},currentRow:1,selectRow:[1],table:[{id:1,name:"",date:"",spec:"",units:"",sums:"",price:"",amount:"",brand:"",remark:""}],tableDataForm:[],defaultTableRow:{id:1,name:"",date:"",spec:"",units:"",sums:"",price:"",amount:"",brand:"",remark:""}}},methods:{addRow:function(){++this.currentRow;var e=JSON.parse(r()(this.defaultTableRow));e.id=this.currentRow,this.table.push(e),this.tableDataForm.push(e),console.log(this.tableDataForm)},deleteRow:function(){var e=this;this.selectRow.forEach(function(t){e.table.some(function(a,l){if(a.id==t)return e.table.splice(l,1),!0})}),this.currentRow-=this.selectRow.length},handleSelectionChange:function(e){var t=[];e.every(function(e){t.push(e.id)}),this.selectRow=t}},created:function(){var e=JSON.parse(r()(this.defaultTableRow));this.tableDataForm.push(e)}},o={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"cash-wappler"},[a("div",{staticClass:"cash-form-container"},[e._m(0),e._v(" "),a("el-form",{ref:"ruleForm",staticClass:"cash-form",attrs:{model:e.form,rules:e.rules,inline:!0,"label-width":"100px"}},[a("el-form-item",{attrs:{label:"项目名称",prop:"name"}},[a("el-input",{model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"联系人",prop:"linkman"}},[a("el-input",{model:{value:e.form.linkman,callback:function(t){e.$set(e.form,"linkman",t)},expression:"form.linkman"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"联系方式",prop:"phone"}},[a("el-input",{model:{value:e.form.phone,callback:function(t){e.$set(e.form,"phone",t)},expression:"form.phone"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"开票税率",prop:"tax"}},[a("el-input",{model:{value:e.form.tax,callback:function(t){e.$set(e.form,"tax",t)},expression:"form.tax"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"配送"}},[a("el-input",{model:{value:e.form.dispatching,callback:function(t){e.$set(e.form,"dispatching",t)},expression:"form.dispatching"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"装卸"}},[a("el-input",{model:{value:e.form.unloading,callback:function(t){e.$set(e.form,"unloading",t)},expression:"form.unloading"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"服务人员"}},[a("el-input",{model:{value:e.form.waiter,callback:function(t){e.$set(e.form,"waiter",t)},expression:"form.waiter"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"报价日期"}},[a("el-date-picker",{attrs:{type:"date",placeholder:"选择日期"},model:{value:e.form.date,callback:function(t){e.$set(e.form,"date",t)},expression:"form.date"}})],1)],1)],1),e._v(" "),a("div",{staticClass:"cash-product"},[e._m(1),e._v(" "),a("div",{staticClass:"table-tool"},[a("el-button",{attrs:{type:"success",size:"mini"},nativeOn:{click:function(t){return e.addRow(t)}}},[e._v("添加行")]),e._v(" "),a("el-button",{attrs:{type:"danger",size:"mini"},nativeOn:{click:function(t){return e.deleteRow(t)}}},[e._v("删除行")]),e._v(" "),a("el-button",{attrs:{type:"warning",size:"mini"}},[e._v("保存")])],1),e._v(" "),a("div",{staticClass:"cash-table-container"},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.table,border:""},on:{"selection-change":e.handleSelectionChange}},[a("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),a("el-table-column",{attrs:{prop:"id",label:"序号"}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"材料名称"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-input",{attrs:{size:"mini","suffix-icon":"el-icon-search",placeholder:"请选择材料名称"},model:{value:e.tableDataForm[t.row.id-1].name,callback:function(a){e.$set(e.tableDataForm[t.row.id-1],"name",a)},expression:"tableDataForm[scope.row.id-1].name"}})]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"spec",label:"规格型号"}}),e._v(" "),a("el-table-column",{attrs:{prop:"units",label:"单位"}}),e._v(" "),a("el-table-column",{attrs:{prop:"sums",label:"数量"}}),e._v(" "),a("el-table-column",{attrs:{prop:"price",label:"单价"}}),e._v(" "),a("el-table-column",{attrs:{prop:"amount",label:"金额"}}),e._v(" "),a("el-table-column",{attrs:{prop:"brand",label:"品牌"}}),e._v(" "),a("el-table-column",{attrs:{prop:"remark",label:"备注"}})],1)],1)])])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"cash-title"},[t("h2",[this._v("基础资料")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"cash-title"},[t("h2",[this._v("产品名称")])])}]};var i=a("VU/8")(n,o,!1,function(e){a("HOob")},"data-v-7940868f",null);t.default=i.exports}});