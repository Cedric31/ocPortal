<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

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

class Hook_members_quiz
{
	/**
	 * Standard modular run function.
	 *
	 * @param  MEMBER		The ID of the member we are getting link hooks for
	 * @return array		List of tuples for results. Each tuple is: type,title,url
	 */
	function run($member_id)
	{
		if (!addon_installed('quizzes')) return array();

		$modules=array();

		if (has_actual_page_access(get_member(),'admin_quiz',get_page_zone('admin_quiz')))
		{
			$modules[]=array('audit',do_lang_tempcode('QUIZ_RESULTS'),build_url(array('page'=>'admin_quiz','type'=>'_quiz_results','member_id'=>$member_id),get_module_zone('admin_quiz')),'menu/cms/quiz/quiz_results');
		}

		return $modules;
	}

	/**
	 * Standard modular get sections function.
	 *
	 * @param  MEMBER		The ID of the member we are getting sections for
	 * @return array		List of sections. Each tuple is Tempcode.
	 */
	function get_sections($member_id)
	{
		if (!addon_installed('quizzes')) return array();

		if (($member_id!=get_member()) && (!has_privilege(get_member(),'view_others_quiz_results'))) return array();

		require_css('quizzes');
		require_lang('quiz');
		require_code('quiz');

		$entries=$GLOBALS['SITE_DB']->query_select(
			'quiz_entries e JOIN '.get_table_prefix().'quizzes q ON q.id=e.q_quiz',
			array('e.id AS e_id','e.q_time','q.*'),
			array('q_member'=>$member_id,'q_type'=>'TEST','q_validated'=>1),
			'ORDER BY e.q_time DESC'
		);
		//$has_points=($GLOBALS['SITE_DB']->query_select_value('quizzes','SUM(q_points_for_passing)',array('q_type'=>'TEST','q_validated'=>1))>0.0);
		$categories=array();
		foreach ($entries as $entry)
		{
			list(
				$marks,
				$potential_extra_marks,
				$out_of,
				,
				,
				,
				,
				,
				,
				$marks_range,
				$percentage_range,
				,
				,
				,
				,
				,
				$passed,
			)=score_quiz($entry['e_id'],$entry['id'],$entry);

			$quiz_name=get_translated_text($entry['q_name']);

			if (strpos($quiz_name,': ')!==false)
			{
				list($category_title,$quiz_name)=explode(': ',$quiz_name,2);
			} else
			{
				$category_title=do_lang('OTHER');
			}

			if (isset($categories[$category_title]['QUIZZES'][$entry['id']])) continue;

			if (!isset($categories[$category_title]))
			{
				$categories[$category_title]=array(
					'QUIZZES'=>array(),
					'RUNNING_MARKS'=>0.0,
					'RUNNING_OUT_OF'=>0,
					'RUNNING_PERCENTAGE'=>0.0,

					// These are not in the template by default. It is used if you are fudging the q_points_for_passing as a credit for full passing of the test.
					//  That's not very normal, but works for people who need more complex course-wide score reporting.
					'RUNNING_MARKS__CREDIT'=>0.0,
					'RUNNING_OUT_OF__CREDIT'=>0,
					'RUNNING_PERCENTAGE__CREDIT'=>0.0,
				);
			}
			/*if (!$has_points)
			{
				$entry['q_points_for_passing']=$out_of;
			}*/
			$categories[$category_title]['QUIZZES'][$entry['id']]=array(
				'QUIZ_NAME'=>$quiz_name,
				'QUIZ_START_TEXT'=>get_translated_tempcode($entry['q_start_text']),
				'QUIZ_ID'=>strval($entry['id']),
				'QUIZ_URL'=>build_url(array('page'=>'quiz','type'=>'do','id'=>$entry['id']),get_module_zone('quiz')),
				'ENTRY_ID'=>strval($entry['e_id']),
				'ENTRY_DATE'=>get_timezoned_date($entry['q_time'],false),
				'_ENTRY_DATE'=>strval($entry['q_time']),
				'OUT_OF'=>strval($out_of),
				'MARKS_RANGE'=>$marks_range,
				'PERCENTAGE_RANGE'=>$percentage_range,
				'PASSED'=>$passed,
				'POINTS'=>strval($entry['q_points_for_passing']),
			);
			$categories[$category_title]['RUNNING_MARKS']+=$marks;
			$categories[$category_title]['RUNNING_OUT_OF']+=$out_of-$potential_extra_marks; /*manually marking discounted to limit us to certainties*/
			$categories[$category_title]['RUNNING_MARKS__CREDIT']+=floatval($entry['q_points_for_passing'])*$marks/floatval($out_of-$potential_extra_marks);
			$categories[$category_title]['RUNNING_OUT_OF__CREDIT']+=$entry['q_points_for_passing'];
		}
		foreach ($categories as &$category)
		{
			$category['RUNNING_PERCENTAGE']=float_to_raw_string(100.0*$category['RUNNING_MARKS']/floatval($category['RUNNING_OUT_OF']));
			$category['RUNNING_MARKS']=float_to_raw_string($category['RUNNING_MARKS']);
			$category['RUNNING_OUT_OF']=strval($category['RUNNING_OUT_OF']);
			if ($category['RUNNING_OUT_OF__CREDIT']==0)
			{
				$category['RUNNING_PERCENTAGE__CREDIT']='0.0';
			} else
			{
				$category['RUNNING_PERCENTAGE__CREDIT']=float_to_raw_string(100.0*$category['RUNNING_MARKS__CREDIT']/floatval($category['RUNNING_OUT_OF__CREDIT']));
			}
			$category['RUNNING_MARKS__CREDIT']=float_to_raw_string($category['RUNNING_MARKS__CREDIT']);
			$category['RUNNING_OUT_OF__CREDIT']=strval($category['RUNNING_OUT_OF__CREDIT']);
		}

		return array(do_template('MEMBER_QUIZ_ENTRIES',array('CATEGORIES'=>$categories,'MEMBER_ID'=>strval($member_id))));
	}
}


