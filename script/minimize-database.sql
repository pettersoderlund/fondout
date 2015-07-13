#
# Shrink the full database to a production friendly size 
# (as small as possible)
#
SET FOREIGN_KEY_CHECKS = 1;

# Delete old fundinstances (latest date for each fund, no funds prior 2014)
delete from fund_instance 
where id not in (
	select fi_inner.fiid from(
		select id as fiid
		from fund_instance fi
		where fi.date = (select max(date) from fund_instance fi2 where fi2.fund = fi.fund)
        and fi.date > '2014-01-01'
	) as fi_inner
);

#Delete funds w/out:
# - fund instances
# - active

delete from fund where id in
(select fund_inner.fid from 
	(select f.id as fid from fund f
	left join fund_instance fi on f.id = fi.fund 
	where (f.active = 0 or f.id is null)
	and f.name not like "AP%"
	and (f.url not like "average%")
	and f.name != "allfunds") fund_inner
);

# Delete shares and shareholdings not used

# Delete all shares that not are part of accusations
delete from share where id not in
	(select share_inner.sid from
		(select s.id as sid
		from share s
		join share_company sc on s.share_company = sc.id
		join share_company_accusation sca on sc.id = sca.share_company_id
		join accusation_category ac on ac.id = sca.accusation_category_id
		where ac.name in ('Förbjudna vapen', 'Fossila bränslen', 'Alkohol', 'Spel', 'Tobak')
	) as share_inner
);

# Delete shares with no parents (no share holdings)
# LAST COMMAND -- PUT THIS IN LAST -- LAST COMMAND
delete from share where id in (
	select share_inner.share_id from
		(select s.id as share_id from share s
		left join shareholding sh on s.id = sh.share
		where sh.id is null) share_inner
	)
;
