# Makefile
# constructs distribution files for Siteframe
# $Id: Makefile,v 1.7 2003/06/25 03:55:04 glen Exp $

build: siteframe.tar.gz

siteframe.tar.gz: siteframe.tar
	gzip -f siteframe.tar

siteframe.tar:
	cvs update ; tar cvf siteframe.tar \
	`find . -type f|grep -v CVS|grep -v Makefile|grep -v '/files'|grep -v ipn.php|grep -v siteframe.tar`

clean:
	rm siteframe.tar.gz 
