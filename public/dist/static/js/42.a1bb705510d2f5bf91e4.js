webpackJsonp([42],{"6UN2":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"wapper"},[i("div",{staticClass:"content-list"},t._l(t.article,function(e,n){return i("div",{key:n,staticClass:"content-item"},[i("div",{staticClass:"ContentItem-avatar"},[i("img",{attrs:{src:e.headimg?e.headimg:"http://e.yhtjc.com/v2/public/img/default.png"}})]),t._v(" "),i("div",{staticClass:"ContentItem-container"},[i("div",{staticClass:"ContentItem-title"},[i("div",{staticClass:"ContentItem-title-text"},[t._v("\n\t\t\t\t\t\t"+t._s(e.title)+"\n\t\t\t\t\t")]),t._v(" "),i("div",{staticClass:"ContentItem-subtitle"},[i("span",{staticClass:"ContentItem-created"},[t._v("创建于:"+t._s(e.created))])])]),t._v(" "),i("div",{staticClass:"ContentItem-actions"},[i("el-button",{attrs:{type:"primary"},nativeOn:{click:function(i){t.edit(e.id)}}},[t._v("编辑")]),t._v(" "),i("el-button",{attrs:{type:"success"},nativeOn:{click:function(i){t.publish(e.id)}}},[t._v("发表")]),t._v(" "),i("el-button",{attrs:{type:"danger"},nativeOn:{click:function(i){t.drop(e.id)}}},[t._v("删除")])],1)])])}))])},staticRenderFns:[]};var s=i("VU/8")({data:function(){return{query:{category_id:0},fixedStyle:{},CategoryPopver:!1,DateSection:0}},methods:{init:function(){this.list(),this.loadCategory()},list:function(){this.$store.dispatch("ArticleDraft",this.query).then(function(){})},loadCategory:function(){this.$store.dispatch("ArticleCategory")},publish:function(t){var e=this;this.$confirm("发表该文章, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"success"}).then(function(){e.$store.dispatch("ArticlePublish",{id:t}).then(function(){"success"==e.$store.state.user.ArticlePublish.status?(e.$notify.success("发布成功"),e.list()):e.$notify.error("发布失败!请重试")})}).catch(function(){})},edit:function(t){this.$router.push("/app/forum/create/article?id="+t)},drop:function(t){var e=this;this.$confirm("删除该文章, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"success"}).then(function(){e.$store.dispatch("ArticleDelete",{id:t}).then(function(){"success"==e.$store.state.user.ArticleDelete.status?(e.$notify.success("删除成功"),e.list()):e.$notify.error("删除失败!请重试")})}).catch(function(){})}},created:function(){this.init()},computed:{article:function(){return this.$store.state.user.ArticleDraft},category:function(){var t=this.$store.state.user.ArticleCategory,e=[{value:0,label:"全部分类"}];for(var i in t){var n={};n.value=t[i].id,n.label=t[i].name,e.push(n)}return e},CurrentCategory:function(){for(var t in this.category)if(this.query.category_id==this.category[t].value)return this.category[t].label},dateList:function(){return[{label:"时间不限",value:0},{label:"一周内",value:1},{label:"三个月内",value:2},{label:"六个月内",value:3}]}}},n,!1,function(t){i("8QZ9")},"data-v-0e06687b",null);e.default=s.exports},"8QZ9":function(t,e){}});