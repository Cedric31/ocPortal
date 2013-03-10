<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		quizzes
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_cms_quiz extends standard_crud_module
{
	var $lang_type='QUIZ';
	var $select_name='NAME';
	var $permissions_require='high';
	var $user_facing=true;
	var $seo_type='quiz';
	var $award_type='quiz';
	var $view_entry_point='_SEARCH:quiz:type=do:id=_ID';
	var $archive_entry_point='_SEARCH:quiz:type=misc';
	var $javascript='var hide_func=function () { var ob=document.getElementById(\'type\'); if (ob.value==\'TEST\') { document.getElementById(\'percentage\').disabled=false; document.getElementById(\'num_winners\').disabled=true; }  if (ob.value==\'COMPETITION\') { document.getElementById(\'num_winners\').disabled=false; document.getElementById(\'percentage\').disabled=true; }  if (ob.value==\'SURVEY\') { document.getElementById(\'text\').value=document.getElementById(\'text\').value.replace(/ \[\*\]/g,\'\'); document.getElementById(\'num_winners\').disabled=true; document.getElementById(\'percentage\').disabled=true; } }; document.getElementById(\'type\').onchange=hide_func; hide_func();';
	var $menu_label='QUIZZES';
	var $table='quizzes';
	var $orderer='q_add_date';

	var $donext_type=NULL;

	/**
	 * Standard modular privilege-override finder function.
	 *
	 * @return array	A map of privileges that are overridable; privilege to 0 or 1. 0 means "not category overridable". 1 means "category overridable".
	 */
	function get_privilege_overrides()
	{
		require_lang('quiz');
		return array('submit_highrange_content'=>array(1,'ADD_QUIZ'),'bypass_validation_highrange_content'=>array(1,'BYPASS_VALIDATION_QUIZ'),'edit_own_highrange_content'=>array(1,'EDIT_OWN_QUIZ'),'edit_highrange_content'=>array(1,'EDIT_QUIZ'),'delete_own_highrange_content'=>array(1,'DELETE_OWN_QUIZ'),'delete_highrange_content'=>array(1,'DELETE_QUIZ'));
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array_merge(array('misc'=>'MANAGE_QUIZZES'),parent::get_entry_points());
	}

	/**
	 * Standard crud_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		set_helper_panel_pic('pagepics/quiz');
		set_helper_panel_tutorial('tut_quizzes');

		require_code('quiz');
		require_code('quiz2');

		$this->add_one_label=do_lang_tempcode('ADD_QUIZ');
		$this->edit_this_label=do_lang_tempcode('EDIT_THIS_QUIZ');
		$this->edit_one_label=do_lang_tempcode('EDIT_QUIZ');
		$this->archive_label='VIEW_ALL_QUIZZES';
		$this->view_label=do_lang_tempcode('TRY_QUIZ');

		if ($type=='misc') return $this->misc();

		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		if (has_actual_page_access(get_member(),'admin_quiz'))
		{
			$also_url=build_url(array('page'=>'admin_quiz'),get_module_zone('admin_quiz'));
			attach_message(do_lang_tempcode('menus:ALSO_SEE_ADMIN',escape_html($also_url->evaluate())),'inform');
		}

		require_code('templates_donext');
		require_code('fields');
		return do_next_manager(get_screen_title('MANAGE_QUIZZES'),comcode_lang_string('DOC_QUIZZES'),
					array_merge(array(
						/*	 type							  page	 params													 zone	  */
						array('add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_QUIZ')),
						array('edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_QUIZ')),
					),manage_custom_fields_donext_link('quiz')),
					do_lang('MANAGE_QUIZZES')
		);
	}

	/**
	 * Standard crud_module table function.
	 *
	 * @param  array			Details to go to build_url for link to the next screen.
	 * @return array			A quartet: The choose table, Whether re-ordering is supported from this screen, Search URL, Archive URL.
	 */
	function nice_get_choose_table($url_map)
	{
		require_code('templates_results_table');

		$current_ordering=get_param('sort','q_name ASC');
		if (strpos($current_ordering,' ')===false) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
		list($sortable,$sort_order)=explode(' ',$current_ordering,2);
		$sortables=array(
			'q_name'=>do_lang_tempcode('TITLE'),
			'q_type'=>do_lang_tempcode('TYPE'),
		);
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');
		inform_non_canonical_parameter('sort');

		$header_row=results_field_title(array(
			do_lang_tempcode('TITLE'),
			do_lang_tempcode('TYPE'),
			do_lang_tempcode('ACTIONS'),
		),$sortables,'sort',$sortable.' '.$sort_order);

		$fields=new ocp_tempcode();

		require_code('form_templates');
		list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering);
		foreach ($rows as $row)
		{
			$edit_link=build_url($url_map+array('id'=>$row['id']),'_SELF');

			$type=do_lang_tempcode($row['q_type']);

			$fields->attach(results_entry(array(protect_from_escaping(hyperlink(build_url(array('page'=>'quiz','type'=>'do','id'=>$row['id']),get_module_zone('quiz')),get_translated_text($row['q_name']))),$type,protect_from_escaping(hyperlink($edit_link,do_lang_tempcode('EDIT'),false,true,'#'.strval($row['id']))))),true);
		}

		$search_url=build_url(array('page'=>'search','id'=>'quiz'),get_module_zone('search'));
		$archive_url=build_url(array('page'=>'quiz'),get_module_zone('quiz'));

		return array(results_table(do_lang($this->menu_label),get_param_integer('start',0),'start',get_param_integer('max',20),'max',$max_rows,$header_row,$fields,$sortables,$sortable,$sort_order),false,$search_url,$archive_url);
	}

	/**
	 * Standard crud_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function nice_get_entries()
	{
		$_m=$GLOBALS['SITE_DB']->query_select('quizzes',array('id','q_name'),NULL,'ORDER BY q_add_date DESC',300);
		$entries=new ocp_tempcode();
		foreach ($_m as $m)
		{
			$entries->attach(form_input_list_entry(strval($m['id']),false,get_translated_text($m['q_name'])));
		}

		return $entries;
	}

	/**
	 * Get tempcode for a adding/editing form.
	 *
	 * @param  ?AUTO_LINK	The quiz ID (NULL: new)
	 * @param  SHORT_TEXT	The name of the quiz
	 * @param  ?integer		The number of minutes allowed for completion (NULL: NA)
	 * @param  LONG_TEXT		The text shown at the start of the quiz
	 * @param  LONG_TEXT		The text shown at the end of the quiz
	 * @param  LONG_TEXT		The text shown at the end of the quiz on failure
	 * @param  LONG_TEXT		Notes
	 * @param  integer		Percentage correctness required for competition
	 * @param  ?TIME			The time the quiz is opened (NULL: now)
	 * @param  ?TIME			The time the quiz is closed (NULL: never)
	 * @param  integer		The number of winners for this if it is a competition
	 * @param  ?integer		The minimum number of hours between attempts (NULL: no restriction)
	 * @param  ID_TEXT		The type
	 * @set    SURVEY COMPETITION TEST
	 * @param  BINARY			Whether this is validated
	 * @param  ?string		Text for questions (NULL: default)
	 * @param  integer		The number of points awarded for completing/passing the quiz/test
	 * @param  ?AUTO_LINK	Newsletter for which a member must be on to enter (NULL: none)
	 * @return tempcode		The form fields
	 */
	function get_form_fields($id=NULL,$name='',$timeout=NULL,$start_text='',$end_text='',$end_text_fail='',$notes='',$percentage=70,$open_time=NULL,$close_time=NULL,$num_winners=2,$redo_time=NULL,$type='COMPETITION',$validated=1,$text=NULL,$points_for_passing=0,$tied_newsletter=NULL)
	{
		if (is_null($open_time)) $open_time=time();

		if (is_null($text))
		{
			$text=do_lang('EXAMPLE_QUESTIONS');
		}

		$fields=new ocp_tempcode();
		$fields->attach(form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('DESCRIPTION_NAME'),'name',$name,true));
		$list=new ocp_tempcode();
		$list->attach(form_input_list_entry('SURVEY',$type=='SURVEY',do_lang_tempcode('SURVEY')));
		$list->attach(form_input_list_entry('TEST',$type=='TEST',do_lang_tempcode('TEST')));
		$list->attach(form_input_list_entry('COMPETITION',$type=='COMPETITION',do_lang_tempcode('COMPETITION')));
		$fields->attach(form_input_list(do_lang_tempcode('TYPE'),do_lang_tempcode('DESCRIPTION_QUIZ_TYPE'),'type',$list,NULL,true));
		$fields->attach(form_input_huge(do_lang_tempcode('QUESTIONS'),do_lang_tempcode('IMPORT_QUESTIONS_TEXT'),'text',$text,true));
		if ($validated==0)
		{
			inform_non_canonical_parameter('validated');

			$validated=get_param_integer('validated',0);
			if (($validated==1) && (addon_installed('unvalidated'))) attach_message(do_lang_tempcode('WILL_BE_VALIDATED_WHEN_SAVING'));
		}
		if (addon_installed('unvalidated'))
			$fields->attach(form_input_tick(do_lang_tempcode('VALIDATED'),do_lang_tempcode('DESCRIPTION_VALIDATED_SIMPLE'),'validated',$validated==1));

		$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'43499b3d39e5743f27852e84cd6d3296','TITLE'=>do_lang_tempcode('TEST'))));
		$fields->attach(form_input_integer(do_lang_tempcode('COMPLETION_PERCENTAGE'),do_lang_tempcode('DESCRIPTION_COMPLETION_PERCENTAGE'),'percentage',$percentage,true));

		$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'9df4bf6d913b68f9c80312df875367d7','TITLE'=>do_lang_tempcode('TEXT'),'SECTION_HIDDEN'=>$start_text=='' && $end_text=='')));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('QUIZ_START_TEXT'),do_lang_tempcode('DESCRIPTION_QUIZ_START_TEXT'),'start_text',$start_text,false));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('QUIZ_END_TEXT'),do_lang_tempcode('DESCRIPTION_QUIZ_END_TEXT'),'end_text',$end_text,false));

		$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'40f0d67ae21fd3768cc7688d90c99d6e','TITLE'=>do_lang_tempcode('COMPETITION'))));
		$fields->attach(form_input_integer(do_lang_tempcode('NUM_WINNERS'),do_lang_tempcode('DESCRIPTION_NUM_WINNERS'),'num_winners',$num_winners,true));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('QUIZ_END_TEXT_FAIL'),do_lang_tempcode('DESCRIPTION_QUIZ_END_TEXT_FAIL'),'end_text_fail',$end_text_fail,false));

		$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array(
			'_GUID'=>'00b9a6a21eab07864d41d5465d9569cd',
			'SECTION_HIDDEN'=>is_null($redo_time) && is_null($timeout) && ((is_null($open_time)) || ($open_time<=time())) && is_null($close_time) && $points_for_passing==0 && is_null($tied_newsletter) && $notes=='',
			'TITLE'=>do_lang_tempcode('ADVANCED'),
		)));
		$fields->attach(form_input_integer(do_lang_tempcode('REDO_TIME'),do_lang_tempcode('DESCRIPTION_REDO_TIME'),'redo_time',$redo_time,false));
		$fields->attach(form_input_integer(do_lang_tempcode('TIMEOUT'),do_lang_tempcode('DESCRIPTION_QUIZ_TIMEOUT'),'timeout',$timeout,false));
		$fields->attach(form_input_date(do_lang_tempcode('OPEN_TIME'),do_lang_tempcode('DESCRIPTION_OPEN_TIME'),'open_time',false,false,true,$open_time,2));
		$fields->attach(form_input_date(do_lang_tempcode('CLOSE_TIME'),do_lang_tempcode('DESCRIPTION_CLOSE_TIME'),'close_time',true,is_null($close_time),true,is_null($close_time)?(NULL/*time()+60*60*24*30*/):$close_time,2));
		if (addon_installed('points'))
		{
			$fields->attach(form_input_integer(do_lang_tempcode('POINTS_FOR_COMPLETING'),do_lang_tempcode('DESCRIPTION_POINTS_FOR_COMPLETING'),'points_for_passing',$points_for_passing,true));
		}
		if (addon_installed('newsletter'))
		{
			$newsletters=new ocp_tempcode();
			$newsletters->attach(form_input_list_entry('',false,do_lang_tempcode('NONE_EM')));
			$_newsletters=$GLOBALS['SITE_DB']->query_select('newsletters',array('*'),NULL,'ORDER BY id');
			foreach ($_newsletters as $n)
			{
				$newsletters->attach(form_input_list_entry(strval($n['id']),$tied_newsletter==$n['id'],get_translated_text($n['title'])));
			}
			if (!$newsletters->is_empty())
				$fields->attach(form_input_list(do_lang_tempcode('TIED_TO_NEWSLETTER'),do_lang_tempcode('DESCRIPTION_TIED_TO_NEWSLETTER'),'tied_newsletter',$newsletters,NULL,false,false));
		}
		if (get_value('disable_staff_notes')!=='1')
			$fields->attach(form_input_text(do_lang_tempcode('NOTES'),do_lang_tempcode('DESCRIPTION_NOTES'),'notes',$notes,false));

		$fields->attach(meta_data_get_fields('quiz',is_null($id)?NULL:strval($id)));

		if (addon_installed('content_reviews'))
			$fields->attach(content_review_get_fields('quiz',is_null($id)?NULL:strval($id)));

		return $fields;
	}

	/**
	 * Standard crud_module submitter getter.
	 *
	 * @param  ID_TEXT		The entry for which the submitter is sought
	 * @return array			The submitter, and the time of submission (null submission time implies no known submission time)
	 */
	function get_submitter($id)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('quizzes',array('q_submitter','q_add_date'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$rows)) return array(NULL,NULL);
		return array($rows[0]['q_submitter'],$rows[0]['q_add_date']);
	}

	/**
	 * Standard crud_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return tempcode		The fields
	 */
	function fill_in_edit_form($_id)
	{
		$id=intval($_id);

		$myrows=$GLOBALS['SITE_DB']->query_select('quizzes',array('*'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$myrows))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$myrow=$myrows[0];

		$text='';
		$question_rows=$GLOBALS['SITE_DB']->query_select('quiz_questions',array('*'),array('q_quiz'=>$id),'ORDER BY q_order');
		foreach ($question_rows as $q)
		{
			$answer_rows=$GLOBALS['SITE_DB']->query_select('quiz_question_answers',array('*'),array('q_question'=>$q['id']),'ORDER BY q_order');
			$text.=get_translated_text($q['q_question_text']).(($q['q_long_input_field']==1)?' [LONG]':'').(($q['q_required']==1)?' [REQUIRED]':'').((($q['q_num_choosable_answers']==count($answer_rows)) && ($q['q_num_choosable_answers']!=0))?' [*]':'').chr(10);
			foreach ($answer_rows as $a)
			{
				$text.=get_translated_text($a['q_answer_text']).(($a['q_is_correct']==1)?' [*]':'').chr(10);
				$explanation=get_translated_text($a['q_explanation']);
				if ($explanation!='')
				{
					$text.=':'.$explanation.chr(10);
				}
			}
			$text.=chr(10);
		}

		return $this->get_form_fields($myrow['id'],get_translated_text($myrow['q_name']),$myrow['q_timeout'],get_translated_text($myrow['q_start_text']),get_translated_text($myrow['q_end_text']),get_translated_text($myrow['q_end_text_fail']),$myrow['q_notes'],$myrow['q_percentage'],$myrow['q_open_time'],$myrow['q_close_time'],$myrow['q_num_winners'],$myrow['q_redo_time'],$myrow['q_type'],$myrow['q_validated'],$text,$myrow['q_points_for_passing'],$myrow['q_tied_newsletter']);
	}

	/**
	 * Standard crud_module add actualiser.
	 *
	 * @return ID_TEXT			The ID of the new entry
	 */
	function add_actualisation()
	{
		$open_time=get_input_date('open_time');
		$close_time=get_input_date('close_time');

		$validated=post_param_integer('validated',0);

		$_tied_newsletter=post_param('tied_newsletter','');
		$tied_newsletter=($_tied_newsletter=='')?NULL:intval($_tied_newsletter);
		$name=post_param('name');

		$meta_data=actual_meta_data_get_fields('quiz',NULL);

		$id=add_quiz($name,post_param_integer('timeout',NULL),post_param('start_text'),post_param('end_text'),post_param('end_text_fail'),post_param('notes',''),post_param_integer('percentage',0),$open_time,$close_time,post_param_integer('num_winners',0),post_param_integer('redo_time',NULL),post_param('type'),$validated,post_param('text'),NULL,post_param_integer('points_for_passing',0),$tied_newsletter,$meta_data['add_time']);

		if ($validated==1)
		{
			if (has_actual_page_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'quiz'))
			{
				require_code('activities');
				syndicate_described_activity('quiz:ACTIVITY_ADD_QUIZ',$name,'','','_SEARCH:quiz:view:'.strval($id),'','','quizzes');
			}
		}

		if (addon_installed('content_reviews'))
			content_review_set('quiz',strval($id));

		return strval($id);
	}

	/**
	 * Standard crud_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($_id)
	{
		$id=intval($_id);

		$open_time=fractional_edit()?INTEGER_MAGIC_NULL:get_input_date('open_time');
		$close_time=fractional_edit()?INTEGER_MAGIC_NULL:get_input_date('close_time');

		$_tied_newsletter=post_param('tied_newsletter','');
		$tied_newsletter=($_tied_newsletter=='')?NULL:intval($_tied_newsletter);
		if (fractional_edit()) $tied_newsletter=INTEGER_MAGIC_NULL;

		$name=post_param('name',STRING_MAGIC_NULL);
		$validated=post_param_integer('validated',fractional_edit()?INTEGER_MAGIC_NULL:0);

		if (($validated==1) && ($GLOBALS['SITE_DB']->query_select_value('quizzes','q_validated',array('id'=>$id))==0)) // Just became validated, syndicate as just added
		{
			$submitter=$GLOBALS['SITE_DB']->query_select_value('quizzes','q_submitter',array('id'=>$id));

			if (has_actual_page_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'quiz'))
			{
				require_code('activities');
				syndicate_described_activity(($submitter!=get_member())?'quiz:ACTIVITY_VALIDATE_QUIZ':'quiz:ACTIVITY_ADD_QUIZ',$name,'','','_SEARCH:quiz:view:'.strval($id),'','','quizzes',1,$submitter);
			}
		}

		$meta_data=actual_meta_data_get_fields('quiz',strval($id));

		edit_quiz(
			$id,
			$name,
			post_param_integer('timeout',fractional_edit()?INTEGER_MAGIC_NULL:NULL),
			post_param('start_text',STRING_MAGIC_NULL),
			post_param('end_text',STRING_MAGIC_NULL),
			post_param('end_text_fail',STRING_MAGIC_NULL),
			post_param('notes',fractional_edit()?STRING_MAGIC_NULL:''),
			post_param_integer('percentage',fractional_edit()?INTEGER_MAGIC_NULL:0),
			$open_time,
			$close_time,
			post_param_integer('num_winners',fractional_edit()?INTEGER_MAGIC_NULL:0),
			post_param_integer('redo_time',fractional_edit()?INTEGER_MAGIC_NULL:NULL),
			post_param('type',STRING_MAGIC_NULL),
			$validated,
			post_param('text',STRING_MAGIC_NULL),
			post_param('meta_keywords',fractional_edit()?STRING_MAGIC_NULL:''),
			post_param('meta_description',fractional_edit()?STRING_MAGIC_NULL:''),
			post_param_integer('points_for_passing',fractional_edit()?INTEGER_MAGIC_NULL:0),
			$tied_newsletter,
			$meta_data['add_time'],
			$meta_data['submitter'],
			true
		);

		if (addon_installed('content_reviews'))
			content_review_set('quiz',strval($id));
	}

	/**
	 * Standard crud_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($_id)
	{
		$id=intval($_id);

		delete_quiz($id);
	}

}


