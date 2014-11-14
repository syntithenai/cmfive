$.fn.rad=function(method) {
	var methods={
		init:function(options) {
			// private internal copy of options for this instance of the plugin
			var settings=$.extend(true,{},$.fn.rad.defaultOptions);
			// init parameters
			if(options) {
				$.extend(true,settings, options);
			}
			return this.each(function() {
				var plugin = this;
				plugin.settings = settings;

				/*************************************************************
				 * PRIVATE METHODS
				 ************************************************************/
	
				/*************************************************************
				 * GATHER DATA FROM DOM METHODS
				 ************************************************************/
				 /*************************************************************
				 * Return array containing two elements - classname and title template
				 ************************************************************/
				function getTableConfig() {
					var title=$('[data-id="metadata"] [data-metadata="titleTemplate"]',plugin).val();
					if (title) title=title.substr(1);
					var table=$('[data-id="metadata"] [data-metadata="className"]',plugin).val();
					var configParts=[table,title];
					return configParts;
				}
	
				/*************************************************************
				 * Convert text in search input into REST query eg /name__like/joe
				 ************************************************************/
				function searchInputToQuery() {
					var finalQuery=[];
					// CONVERT searchFields title,data and search box value ba gs into REST search URL
					// /OR/AND/title__like/ba/title__like/gs/END/AND/data_like/ba/data_like/gs/END/END
					var query=$('[data-id="searchinput"]',plugin).val();
					if (query) {
						query=query.replace("/","").replace("%","").replace("?","");
					} else {
						query="";
					}
					var searchFields=$('[data-id="metadata"] [data-metadata="searchFields"]',plugin).val();
					var searchFieldParts=[];
					if (searchFields && searchFields.length>0) searchFieldParts=searchFields.split(",");
					if (query && query.length>0) {
						var queryParts=query.split(' ');
						finalQuery.push("OR");
						$.each(searchFieldParts,function(sfk,sfv) {
							finalQuery.push("AND");
							$.each(queryParts,function(k,v) {
								if ($.trim(v).length>0) {
									finalQuery.push(sfv+'___contains');
									finalQuery.push($.trim(v));
								}
							});
							finalQuery.push("END");
						});
						finalQuery.push("END");
					}
					return finalQuery;
				}
				/*************************************************************
				 * Convert query builder rules into REST query eg /name__like/joe
				 ************************************************************/
				function queryBuilderToQuery(rule) {
					var query=[];
					if ((rule.condition=='AND'||rule.condition=="OR") && rule.rules && rule.rules.length && rule.rules.length>0) {
						query.push(rule.condition);
						$.each(rule.rules,function(k,irule) {
							var iRuleSet=queryBuilderToQuery(irule);
							$.each(iRuleSet,function(irk,irv) {
								query.push(irv);
							});	
						});
						query.push('END');
					} else {
						query.push(rule.id+'___'+rule.operator);
						if (rule.value && $.isArray(rule.value)) {
							query.push(rule.value.join("___")); 
						} else {
							query.push(rule.value); 
						}
					}
					return query;
				}
				/*************************************************************
				 * METHODS TO MAKE AJAX REQUESTS TO REST API
				 * all return Deferred
				 ************************************************************/
				/*************************************************************
				 * Insert or update a record 
				 * @param record - array containing property data
				 ************************************************************/
				function saveRecord(record) {
					var dfr=$.Deferred();
					var configParts=getTableConfig();
					startWaiting();
					$.ajax('/rest/save/'+configParts[0]+'/?token='+sessionStorage.session,{type:'POST',data:record,dataType:'json',complete:function(res) {	
						stopWaiting();
						if (res && res.responseJSON && res.responseJSON.error) {
							dfr.reject(res.responseJSON.error);
						} else {
							dfr.resolve(res.success);
						}
					},error:function(req,msg,err) {
						dfr.reject('Server error');	
					}});
					
					return dfr;
				}
				
				/*************************************************************
				 * Request records matching query 
				 * @param record - query array
				 ************************************************************/
				function loadResults(finalQuery) {
					var dfr=$.Deferred();
					var configParts=getTableConfig();
					if (configParts && configParts[0] && configParts[0].length>0) {
						var query=finalQuery.join('/');
						startWaiting();
						$.ajax('/rest/index/'+configParts[0]+'/'+query+'?token='+sessionStorage.session,{dataType:'json',error:function(req,msg,err) {
							dfr.reject('Server error');	
						},complete:function(res) {
							stopWaiting();
							if (res && res.responseJSON && res.responseJSON.error) {
								dfr.reject(res.responseJSON.error);
							} else {
								if (res && res.responseJSON) {
									dfr.resolve(res.responseJSON.success);
								} else {
									dfr.resolve();
								}
							}
						}});
					} else {
						dfr.reject('You must select a search table');
					}
					return dfr;
				}
				/*************************************************************
				 * Delete a record
				 * @param id - of record to delete
				 * @param row - DOM of list row that is being deleted
				 ************************************************************/
				function deleteRecord(id,row) {
					var dfr=$.Deferred();
					var configParts=getTableConfig();
					if (confirm('Really delete record ?')) {
						startWaiting();
						var cKey=$('#CSRF').data('field');
						var cVal=$('#CSRF').val();
						var data={};
						data[cKey]=cVal;
						$.ajax('/rest/delete/'+configParts[0]+'/'+id+'?token='+sessionStorage.session,{data:data,type:'POST',dataType:'json',error:function(req,msg,err) {
							dfr.reject('Server error');	
						},complete:function(res) {
							stopWaiting();
							if (res && res.responseJSON && res.responseJSON.error) {
								dfr.reject(res.responseJSON.error);
							} else {
								row.remove();
								dfr.resolve();
							}
						}});
					}
					return dfr;
				}
				/*************************************************************
				 * Login request
				 * @param api - REST api key
				 * @param username
				 * @param pasword
				 ************************************************************/
				function auth(api,username,password) {
					var dfr=$.Deferred();
					if (sessionStorage.session && sessionStorage.session.length && sessionStorage.session.length>0) {
						dfr.resolve();
					} else {
						$.ajax('/rest/token/?',{data:{username:username,password:password,api:api},dataType:'json',error:function(req,msg,err) {
							dfr.reject('Server error');	
						},complete:function(res) {
							if (res && res.responseJSON && res.responseJSON.error) {
								dfr.reject(res.responseJSON.error);
							} else {
								sessionStorage.session=res.responseJSON.success;
								dfr.resolve();
							}
						}});
					}
					return dfr;
				}
				/*************************************************************
				 * DOM MANIPULATION
				 ************************************************************/
				/*************************************************************
				 * Add a query builder widget to the page
				 * Information from [data-id="metadata"] is used to configure the query builder
				 ************************************************************/
				function initialiseQueryBuilder() {
					var searchFields=$('[data-id="metadata"] [data-metadata="searchFields"]',plugin).val();
					if (searchFields) searchFields=searchFields.split(",") 
					else searchFields=[];
					var properties=$('[data-id="metadata"] [data-metadata="databaseColumns"]',plugin).val();
					if (properties) properties=properties.split(",") 
					else properties=[];
					var labels=$('[data-id="metadata"] [data-metadata="labels"]',plugin).val();
					if (labels) labels=labels.split(",") 
					else labels=[];
					var types=$('[data-id="metadata"] [data-metadata="propertyUITypes"]',plugin).val();
					if (types) types=types.split(",") 
					else types=[];
					if (properties && labels && types && properties.length>0 && properties.length==labels.length && properties.length==types.length)  {
						// collate table meta data
						var meta={};
						for (var i=0; i < properties.length; i++) {
							meta[properties[i]]={'label':labels[i],'type':types[i]};
						}
						var filters=[];
						var rules=[];
						var count=0;
						// use meta data to configure builder filters
						$.each(searchFields,function(k,v) {
							var filter={'id':v,'label':meta[v]['label'],'type':'string'};//meta[v]['type']
							if (count==0)  rules.push({ id: v, operator: 'equal',value: ''});
							filters.push(filter); 
							count++;
						});
						$('[data-id="querybuilder"]',plugin).queryBuilder({
							filters: filters,
							sortable:true,
							// add styles for foundation
							onAfterAddRule:function() {
								$('[data-id="querybuilder"] button,[data-id="querybuilder"] .btn',plugin).addClass('button').addClass('tiny');
							},onAfterAddGroup:function() {
								$('[data-id="querybuilder"] button,[data-id="querybuilder"] .btn',plugin).addClass('button').addClass('tiny');
							}
						});
						$('[data-id="querybuilder"]',plugin).queryBuilder('setRules', {condition: 'AND',rules: rules});
						//$('[data-id="querybuilder"] button,[data-id="querybuilder"] .btn').addClass('button').addClass('tiny');
						// ADD BUTTONS TO QUERY BUILDER - NEW, SIMPLE SEARCH, SEARCH
						var buttonSet=$('<buttonset class="right"></buttonset>');
						var simpleButton=$('<input type="submit" data-action="simplesearch" value="Simple Search" class="button tiny"/>');
						var newButton=$('<input data-action="new" class="newbutton button tiny" type="button" value="New" >');
						var searchButton=$('<input class="button tiny" type="button" data-action="search" value="Search" >');
						buttonSet.append(simpleButton).append(searchButton).append(newButton);
						$('[data-id="querybuilder"] .rules-group-container div.pull-right',plugin).first().append(buttonSet);
						// BIND CLICK EVENTS TO THESE BUTTONS
						simpleButton.on('click',function(e) {
							$('[data-id="querybuilder"]',plugin).hide();
							$('[data-id="search"] [data-action="advancedsearch"]',plugin).show();
							$('[data-id="searchform"]',plugin).show();
							return false;
						});
						if (!$('[data-id="metadata"] [data-metadata="canCreate"]',plugin).val()) 
						$('.newbutton',plugin).hide();
						newButton.on('click',function(e) {
							fillEditForm({});
							$('[data-id="editform"] h3',plugin).html('New Record');
							$('[data-id="editform"]',plugin).show();
							$('[data-id="search"]',plugin).hide();
							return false;
						});
						searchButton.on('click',function(e) {
							loadResults(queryBuilderToQuery($('[data-id="querybuilder"]',plugin).queryBuilder('getRules'))).then(function(res) {
								$('[data-id="searchresults"] .searchresultsrow',plugin).remove();
								$.each(res,function(k,v) {
									fillListRow(v);
								});
							});
							$('[data-id="searchresults"]',plugin).show();
							return false;
						});
					} else {
						console.log('metadata error, properties/labels/types dont match up');
					}
				}
				/*************************************************************
				 * Add a message to the #messages element for three seconds and then remove it
				 ************************************************************/
				function flashMessage(msg) {
					var msgDOM=$('<div>'+msg+'</div>');
					$('#message').append(msgDOM);
					setTimeout(function() {
						msgDOM.remove();
					},3000);
				}
				/*************************************************************
				 * Fill [data-id="editform"] inputs with attribute data-role=editfield with information from record parameter
				 * @ param record - object with properties to map into the edit form using the data-field attribute of form inputs to match
				 ************************************************************/
				function fillEditForm(record) {
					$('[data-id="editform"] [data-role="editfield"],[data-id="editform"] [data-role="hiddenfield"]:not([data-noset="true"])',plugin).each(function() {
						if ($(this).data('field') && $(this).data('field').length && record[$(this).data('field')]) $(this).val(record[$(this).data('field')]);
						else $(this).val('');
					});
					$('[data-id="search"]',plugin).hide();
					$('[data-id="editform"]',plugin).show();
				}
				/*************************************************************
				 * Append or replace into [data-id="searchresults"] a row representing the record parameter
				 * The [data-id="searchresults"] element contains a .searchresultsrowtemplate element that is used to replace markers into
				 * Array keys in the record are matched to HTML elements with attribute data-field='<recordKey>'
				 * Where there is a list row with a matching id, it is replaced with a rerendered row from parameter data
				 * @ param record - object with properties of a record to insert/append to the list
				 ************************************************************/
				function fillListRow(record) {
					var existingRow;
					if (record.id>0) {
						existingRow=$('[data-id="searchresults"] [data-field="id"]:contains("'+record.id+'")',plugin).parents('.searchresultsrow').first();
					}
					var listRowTemplate=$('[data-id="searchresults"] .searchresultsrowtemplate',plugin).first().clone();
					listRowTemplate.addClass('searchresultsrow').removeClass('searchresultsrowtemplate');
					$('[data-field]',listRowTemplate).each(function() {
						if ($(this).data('field').length && record[$(this).data('field')]) $(this).html(record[$(this).data('field')]);
						else $(this).html('');
						listRowTemplate.data('record',record);
					});
					if (existingRow && existingRow.length>0) {
						$(existingRow).replaceWith(listRowTemplate);
					} else {
						$('[data-id="searchresults"]',plugin).append(listRowTemplate);
					}
					$('[data-id="searchresults"] .searchresultsrow',plugin).show();
					if (!$('[data-id="metadata"] [data-metadata="canEdit"]',plugin).val()) $('[data-action="edit"]',listRowTemplate).hide();
					if (!$('[data-id="metadata"] [data-metadata="canDelete"]',plugin).val()) $('[data-action="delete"]',listRowTemplate).hide();
				}
				/*************************************************************
				 * Show waiting animation
				 ************************************************************/
				function startWaiting () {
					$('.loading_overlay').show();
				};
				/*************************************************************
				 * Hide waiting animation
				 ************************************************************/
				function stopWaiting() {
					$('.loading_overlay').hide();
				};
				
				/*************************************************************
				 * EVENT BINDING FOR UI
				 ************************************************************/
				function bindEvents() {
					// SEARCH FORM
					$('[data-id="searchform"] form',plugin).on('submit',function() {
						loadResults(searchInputToQuery()).then(function(res) {
							$('[data-id="searchresults"] .searchresultsrow').remove();
							$.each(res,function(k,v) {
								fillListRow(v);
							});
						});
						$('[data-id="searchresults"]',plugin).show();
						return false;
					});
					// ADVANCE SEARCH
					$('[data-id="search"] [data-action="advancedsearch"]',plugin).on('click',function(e) {
						if ($('[data-id="querybuilder"] .rules-group-container',plugin).length==0) { 
							initialiseQueryBuilder();
						}
						$('[data-id="search"] [data-action="advancedsearch"]',plugin).hide();
						$('[data-id="searchform"]',plugin).hide();
						$('[data-id="querybuilder"]',plugin).show();
							
						return false;
					});
					// NEW BUTTON
					if (!$('[data-id="metadata"] [data-metadata="canCreate"]',plugin).val()) $('.newbutton',plugin).hide();
					$('.newbutton',plugin).on('click',function(e) {
						fillEditForm({});
						$('[data-id="editform"] h3',plugin).html('New Record');
						$('[data-id="editform"]',plugin).show();
						$('[data-id="search"]',plugin).hide();
						return false;
					});
					// LIST - single event binding on all list elements
					$('[data-id="searchresults"]',plugin).on('click',function(e) {
						var button=$(e.originalEvent.target);
						var action=button.data('action');
						var row=button.parents('.searchresultsrow');
						var record=row.data('record');
						if (record) {
							if (action=='edit') {
								fillEditForm(record);
								$('[data-id="editform"] h3',plugin).html('Edit Record');
								$('[data-id="editform"]',plugin).show();
								$('[data-id="search"]',plugin).hide();
							} else if (action=='delete') {
								deleteRecord(record['id'],row);
							}
						} /*else {
							var id=$('[data-field="id"]',row).html();
							console.log('domid',id);
							if (id)  {				
								if (action=='edit') {
									loadRecord(id).then(function(record) {
										fillEditForm(record);
										$('[data-id="editform"]',plugin).show();
										$('[data-id="search"]',plugin).hide();
									});
								} else if (action=='delete') {
									console.log('del',record);
									deleteRecord(record['id'],row);
								}
							}
						}*/
						return false;
					});
					// EDIT FORM
					$('[data-id="editform"] form',plugin).on('submit',function() {
						return false;
					});
					$('[data-id="editform"] [data-action="save"]',plugin).on('click',function() {
						var record={};
						$('[data-id="editform"] [data-role="editfield"],[data-id="editform"] [data-role="hiddenfield"]',plugin).each(function() {
							if ($(this).data('field') && $(this).data('field').length>0) record[$(this).data('field')]=$(this).val();
						});
						console.log('save',record);
						saveRecord(record).then(function(savedRecord) {
							$('[data-id="editform"]',plugin).hide();
							$('[data-id="search"]',plugin).show();
							flashMessage('Saved');
							// update data stored in list row
							if (record.id>0) {
								var row=$('[data-id="searchresults"] [data-field="id"]',plugin).parents('.searchresultsrow');
								row.data('record',savedRecord);
								fillListRow(record);
							} else {
								$('[data-id="searchform"] [data-action="search"]',plugin).click();
							}
						}).fail(function(err) {
							flashMessage(err);
						});
						return false;
					});
					$('[data-id="editform"] [data-action="close"]',plugin).on('click',function() {
						$('[data-id="editform"]',plugin).hide();
						$('[data-id="search"]',plugin).show();
						return false;
					});
					
				}
				// FINALLY WE GET TO THE TOP LEVEL - WHAT DO WE DO?
				
				auth().then(function() {
					bindEvents();
					$('[data-id="search"]',plugin).show();
					// auto search
					if (plugin.settings.autoSearch) $('[data-id="searchform"] form',plugin).submit();
				}).fail(function(err) {
					flashMessage(err);
				});				
			});
		},
		loadPartial:function(settings) {
			var dfr=$.Deferred();
			var promises=[];
			$(this).each(function() {
				var idfr=$.Deferred();
				promises.push(idfr);
				var plugin = this;
				$(plugin).load('/rest/partial/'+settings.module+'/'+settings.partial+'/'+settings.class+'',function(res) { 
					idfr.resolve();
				});
			});
			$.when.apply($,promises).then(function() {
				dfr.resolve();
			});
			return dfr;
		} 
	};
	
	if( $.isFunction(methods[method])) {
		return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
	} 
	else if(typeof method === 'object' || !method) {
		return methods.init.apply(this, arguments);
	} 
	else {
		$.error("Method " +  method + " does not exist on jQuery.rad");
	}    
	
	
}
