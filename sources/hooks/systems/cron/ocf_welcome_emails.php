<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		welcome_emails
 */

class Hook_cron_ocf_welcome_emails
{

	/**
	 * Standard modular run function for CRON hooks. Searches for tasks to perform.
	 */
	function run()
	{
		$time_now=time();
		$last_cron_time=intval(get_value('last_welcome_mail_time'));
		if ($last_cron_time==0) $last_cron_time=time()-24*60*60*7;
		set_value('last_welcome_mail_time',strval($time_now));

		require_code('mail');

		$GLOBALS['NO_DB_SCOPE_CHECK']=true;
		$mails=$GLOBALS['SITE_DB']->query_select('f_welcome_emails',array('*'));
		$GLOBALS['NO_DB_SCOPE_CHECK']=false;
		foreach ($mails as $mail)
		{
			$send_seconds_after_joining=$mail['w_send_time']*60*60;

			$newsletter_style=((get_value('welcome_nw_choice')==='1') && (!is_null($mail['w_newsletter']))) || ((get_value('welcome_nw_choice')!=='1') && (($mail['w_newsletter']==1) || (get_forum_type()!='ocf')));
			if ($newsletter_style)
			{
				if (addon_installed('newsletter'))
				{
					// Think of it like this, m_join_time (members join time) must between $last_cron_time and $time_now, but offset back by $send_seconds_after_joining
					$where=' WHERE join_time>'.strval($last_cron_time-$send_seconds_after_joining).' AND join_time<='.strval($time_now-$send_seconds_after_joining).' AND (the_level=3 OR the_level=4)';
					if (get_value('welcome_nw_choice')==='1')
					{
						$where.=' AND newsletter_id='.strval($mail['w_newsletter']);
					}
					$members=$GLOBALS['SITE_DB']->query('SELECT s.email AS m_email_address,the_password,n_forename,n_surname,n.id FROM '.get_table_prefix().'newsletter_subscribe s JOIN '.get_table_prefix().'newsletter n ON n.email=s.email '.$where.' GROUP BY s.email');
				} else
				{
					$members=array();
				}
			} else
			{
				// Think of it like this, m_join_time (members join time) must between $last_cron_time and $time_now, but offset back by $send_seconds_after_joining
				$where=' WHERE m_join_time>'.strval($last_cron_time-$send_seconds_after_joining).' AND m_join_time<='.strval($time_now-$send_seconds_after_joining);
				if (get_option('allow_email_disable')=='1') $where.=' AND m_allow_emails=1';
				$query='SELECT m_email_address,m_username,id FROM '.get_table_prefix().'f_members'.$where;
				$members=$GLOBALS['FORUM_DB']->query($query);
			}

			foreach ($members as $member)
			{
				$subject=get_translated_text($mail['w_subject'],NULL,get_lang($member['id']));
				$text=get_translated_text($mail['w_text'],NULL,get_lang($member['id']));
				$_text=do_template('NEWSLETTER_DEFAULT',array('CONTENT'=>$text,'LANG'=>get_site_default_lang()));
				for ($i=0;$i<100;$i++)
				{
					if (strpos($text,'{{'.strval($i).'}}')!==false)
						$text=str_replace('{{'.strval($i).'}}',get_timezoned_date(time()+$i*60*60*24),$text);
				}

				if ($member['m_email_address']!='')
				{
					$message=$_text->evaluate(get_lang($member['id']));
					
					if ($newsletter_style)
					{
						$forename=$member['n_forename'];
						$surname=$member['n_surname'];
						$name=trim($forename.' '.$surname);
						require_lang('newsletter');
						if ($name=='') $name=do_lang('NEWSLETTER_SUBSCRIBER',get_site_name());
					} else
					{
						$forename='';
						$surname='';
						$name=$member['m_username'];
					}

					if (addon_installed('newsletter'))
					{
						if ($newsletter_style)
						{
							$sendid='n'.strval($member['id']);
							$hash=md5('xunsub'.$member['the_password']);
						} else
						{
							$sendid='w'.strval('id');
							$hash='';
						}

						require_code('newsletter');
						$message=newsletter_variable_substitution($message,$subject,$forename,$surname,$name,$member['m_email_address'],$sendid,$hash);
					}
					mail_wrap($subject,$message,array($member['m_email_address']),$name,'','',3,NULL,false,NULL,true);
				}
			}
		}
	}

}


