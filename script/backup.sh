rsync -ave 'ssh -p 3355' web@houstonbbs.com:/home/backup/server_conf*tar.gz /data/backup/houstonbbs/conf/
rsync -ave 'ssh -p 3355' web@houstonbbs.com:bbs/log/{access,error}_*.log-*.gz /data/backup/houstonbbs/log/
rsync -ave 'ssh -p 3355' web@houstonbbs.com:bbs/backup/*.sql.gz /data/backup/houstonbbs/db/
