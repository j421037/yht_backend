webpackJsonp([9],{"9Cxd":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=i("mvHQ"),r=i.n(a),s={data:function(){return{defaultMsg:"",titlePicBase64:"",showTitlePic:!1,article:{id:"",attr:"protected",title:"",body:"",status:0,category:0,module_id:0},AttrErrStatus:!1,CategoryErrStatus:!1,update:!1,defaultArticle:{},hackReset:!1,ArticleAttr:[{label:"公开",value:"public"},{label:"部门可见",value:"protected"}],category:[],ArticleAttrVisible:!0}},methods:{publishArticle:function(){var t=this;this.$confirm("发表该文章, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"success"}).then(function(){t.article.status=1,t.saveArticle()}).catch(function(){})},saveArticle:function(){var t=this,e="ArticlePost";return""==this.article.title||""==this.article.body?(this.$message.error("标题或者内容不能为空!"),!1):this.article.attr?(this.AttrErrStatus=!1,!1===this.article.category?(this.CategoryErrStatus=!0,!1):(this.CategoryErrStatus=!1,this.update&&(e="ArticlePut"),void this.$store.dispatch(e,this.article).then(function(){var e=t.$store.state.user.ArticlePost;t.update&&(e=t.$store.state.user.ArticleUpdate),"success"==e.status?(t.$notify.success("操作成功"),t.update=!1,t.reset()):t.$notify.error("操作失败,请重试")}))):(this.AttrErrStatus=!0,!1)},init:function(){this.defaultArticle=JSON.parse(r()(this.article)),this.loadArticlePersonalModule(),this.isEdit()},isEdit:function(){""!=this.$route.query.id&&void 0!==this.$route.query.id&&(this.update=!0,this.getContent(this.$route.query.id))},getContent:function(t){var e=this;this.$store.dispatch("ArticleEdit",{id:t}).then(function(){var t=e.$store.state.user.ArticleEditOne;if("success"==t.status){var i=t.data;e.article.id=i.id,e.article.body=i.body,e.article.title=i.title,e.article.status=i.status,e.article.attr=i.attr,e.article.module_id=i.module_id,e.article.category=i.category_id}else e.$message.warning(t.errmsg),setTimeout(function(){e.$message.close(),e.$router.push("/app/forum/portal")},2500)})},loadCategory:function(){this.$store.dispatch("ArticleCategory")},loadArticlePersonalModule:function(){this.$store.dispatch("ArticlePersonalModule")},AddTitlePic:function(t){this.article.titlepic=this.$refs[t].files[0],this.titlePicBase64=window.URL.createObjectURL(this.$refs[t].files[0]),this.showTitlePic=!0},RemoveTitlePic:function(t){this.showTitlePic=!1,this.titlePicBase64=null,this.$refs[t].value=null,this.article.titlepic=null},reset:function(){this.article.title="",this.article.body=""},ArticleModuleChange:function(t){var e=this;this.ArticlePersonalModule.forEach(function(i){t==i.value&&("public"==i.attr?e.ArticleAttrVisible=!1:e.ArticleAttrVisible=!0,e.BuildCategory(i.category))})},BuildCategory:function(t){var e=[{label:"默认",value:0}];t.length>0&&t.forEach(function(t){e.push({label:t.label,value:t.value})}),this.category=e}},created:function(){this.init(),this.module_id=this.$route.params.module_id},mounted:function(){var t=this;this.$nextTick(function(){t.hackReset=!0})},computed:{ArticleContent:function(){return this.$store.state.user.ArticleEditOne},ForumModule:function(){var t=[];return this.$store.state.user.ForumModule.forEach(function(e){"public"==e.attr&&e.id>0&&t.push({label:e.name,value:e.id})}),t},ArticlePersonalModule:function(){var t=this.$store.state.user.ArticlePersonalModule;return t.length>0&&(this.article.module_id=t[0].value,this.BuildCategory(t[0].category)),t}},components:{"v-editor":i("bUEx").a}},l={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"wapper"},[i("div",{staticClass:"layout-main"},[t.showTitlePic?i("div",{staticClass:"title-pic",class:{"title-pic-empty":!t.showTitlePic}},[i("div",{directives:[{name:"show",rawName:"v-show",value:!t.showTitlePic,expression:"!showTitlePic"}],staticClass:"previewWrapper"},[i("i",{staticClass:"iconfont icon-xiangji"}),t._v(" "),i("input",{ref:"titlePic",attrs:{type:"file",name:"titlePic"},on:{change:function(e){t.AddTitlePic("titlePic")}}})]),t._v(" "),i("div",{directives:[{name:"show",rawName:"v-show",value:t.showTitlePic,expression:"showTitlePic"}],staticClass:"previewContent"},[i("img",{attrs:{src:t.titlePicBase64}}),t._v(" "),i("div",{staticClass:"WriteCover-editWrapper"},[t._m(0),t._v(" "),i("button",{attrs:{type:"button",title:"删除"},on:{click:function(e){t.RemoveTitlePic("titlePic")}}},[i("i",{staticClass:"iconfont icon-shanchu"})])])])]):t._e(),t._v(" "),i("div",{staticClass:"write-title"},[i("input",{directives:[{name:"model",rawName:"v-model.trim",value:t.article.title,expression:"article.title",modifiers:{trim:!0}}],staticClass:"titleInput",attrs:{placeholder:"请输入标题"},domProps:{value:t.article.title},on:{input:function(e){e.target.composing||t.$set(t.article,"title",e.target.value.trim())},blur:function(e){t.$forceUpdate()}}})]),t._v(" "),i("div",{staticClass:"editor"},[i("v-editor",{attrs:{height:350},model:{value:t.article.body,callback:function(e){t.$set(t.article,"body",e)},expression:"article.body"}})],1),t._v(" "),i("div",{staticClass:"category"},[i("div",{staticClass:"article-attribute"},[i("p",[i("span",[t._v("发  布  到:")]),t._v(" "),i("el-select",{attrs:{placeholder:"请选择要发布的模块"},on:{change:t.ArticleModuleChange},model:{value:t.article.module_id,callback:function(e){t.$set(t.article,"module_id",e)},expression:"article.module_id"}},t._l(t.ArticlePersonalModule,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1)]),t._v(" "),t.ArticleAttrVisible?i("div",{staticClass:"article-attribute"},[i("p",[i("span",[t._v("文章属性:")]),t._v(" "),i("el-select",{attrs:{placeholder:"请选择文章属性"},model:{value:t.article.attr,callback:function(e){t.$set(t.article,"attr",e)},expression:"article.attr"}},t._l(t.ArticleAttr,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1),t._v(" "),i("p",{directives:[{name:"show",rawName:"v-show",value:t.AttrErrStatus,expression:"AttrErrStatus"}],staticClass:"error-msg"},[t._v(" 请选择一个文章的属性")])]):t._e(),t._v(" "),i("div",{staticClass:"article-attribute"},[i("p",[i("span",[t._v("文章分类:")]),t._v(" "),i("el-select",{attrs:{placeholder:"请选择分类"},model:{value:t.article.category,callback:function(e){t.$set(t.article,"category",e)},expression:"article.category"}},t._l(t.category,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1),t._v(" "),i("p",{directives:[{name:"show",rawName:"v-show",value:t.CategoryErrStatus,expression:"CategoryErrStatus"}],staticClass:"error-msg"},[t._v(" 请选择文章的分类")])])]),t._v(" "),t.update?i("div",{staticClass:"write-tool"},[i("el-button",{attrs:{type:"warning",size:"medium",disabled:""==t.article.body},on:{click:t.saveArticle}},[t._v("保存修改\n            ")]),t._v(" "),0==t.article.status?i("el-button",{attrs:{type:"success",size:"medium",disabled:""==t.article.body},on:{click:t.publishArticle}},[t._v("发表文章\n            ")]):t._e()],1):i("div",{staticClass:"write-tool"},[i("el-button",{attrs:{type:"warning",size:"medium",disabled:""==t.article.body},on:{click:t.saveArticle}},[t._v("保存到草稿箱\n            ")]),t._v(" "),i("el-button",{attrs:{type:"success",size:"medium",disabled:""==t.article.body},on:{click:t.publishArticle}},[t._v("发表文章\n            ")])],1),t._v(" "),i("div",{staticClass:"write-tool"},[i("el-alert",{attrs:{title:"使用说明",type:"info",closable:!1}},[t._t("default",[i("p",[t._v("1、文章的属性分为公开和部门内成员可见")]),t._v(" "),i("p",[t._v("2、公开的属性：其他部门的同事可以浏览该文章")]),t._v(" "),i("p",[t._v("3、部门可见的属性：只有当前部门内的成员才可以浏览见该文章")]),t._v(" "),i("p",[t._v("4、文章发布到非本部门的区域，其属性都是公开的")])])],2)],1)])])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("button",{attrs:{type:"button",title:"更换"}},[e("i",{staticClass:"iconfont icon-xiangji"})])}]};var o=i("VU/8")(s,l,!1,function(t){i("YKtP")},"data-v-5f0b96a8",null);e.default=o.exports},YKtP:function(t,e){},bUEx:function(t,e,i){"use strict";var a=i("fZjL"),r=i.n(a),s=(i("9fd9"),{props:{PropConfig:{type:Object,default:function(){return{}}},value:{default:""},height:{type:Number,default:350}},data:function(){return{froalaConfig:{pluginsEnabled:["align","charCounter","codeBeautifier","colors","draggable","entities","file","fontAwesome","fontFamily","fontSize","image","imageTUI","imageManager","inlineStyle","inlineClass","lineBreaker","lineHeight","link","lists","paragraphFormat","paragraphStyle","quote","table","url","video","wordPaste"],toolbarButtons:["undo","redo","clearFormatting","|","bold","italic","underline","strikeThrough","|","fontFamily","fontSize","color","|","paragraphFormat","align","formatOL","formatUL","outdent","indent","|","quote","insertLink","insertImage","insertVideo","insertTable","specialCharacters","insertHR","insertFile"],theme:"dark",placeholder:"请输入内容",language:"zh_cn",fileUpload:!0,fileInsertButtons:["fileBack","|"],fileUploadMethod:"POST",fileUploadParam:"uploadfile",fileUploadParams:{id:"my_editor"},imageUploadURL:this.$appConst.EDITOR_IMG_URL+"?token="+this.$store.state.user.token,fileUploadURL:this.$appConst.FILE_UPLOAD_URL+"?token="+this.$store.state.user.token,disableRightClick:!0,colorsHEXInput:!0,toolbarSticky:!0,width:"100%",height:this.height,charCounterMax:-1}}},created:function(){if(r()(this.PropConfig).length>0)for(var t in this.PropConfig)this.editorConfig[t]=this.PropConfig[t]},computed:{content:{get:function(){return this.value},set:function(t){this.$emit("input",t)}}},methods:{onEditorReady:function(){this.$el.querySelector(".ck-content").style.height=this.height+"px"}}}),l={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{ref:"editor",staticClass:"editor-container"},[i("froala",{attrs:{tag:"textarea",config:t.froalaConfig},model:{value:t.content,callback:function(e){t.content=e},expression:"content"}})],1)},staticRenderFns:[]},o=i("VU/8")(s,l,!1,null,null,null);e.a=o.exports}});