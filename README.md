jobtest
=======

A Symfony project created on October 6, 2015, 9:25 am.

http://intelligentbee.com/blog/2013/08/07/symfony2-jobeet-day-1-starting-up-the-project/

to generate code covearge docs you need tou use php unit command
`phpunit --coverage-html=code_docs/ -c app/`
code_docs folder added to git ignore

create command for cleanup jobs
configure cron with 
* 1 * * * cd [project_path] && php app/console ibw:jobeet:cleanup