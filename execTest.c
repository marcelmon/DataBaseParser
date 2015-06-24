#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdbool.h>
#include <sys/types.h>

int main() {



	

	int pipes[2];

 pipe(pipes); // Create the pipes

 dup2(pipe[1],1);


	char path[1035];

	if(pid == 0) {
		printf("HEY");
		dup2 (link[1], STDOUT_FILENO);
    	close(link[0]);
    	close(link[1]);
		execl("/bin/sh" , 'httrack \"http://individual.utoronto.ca/marcel91\" \"/home/testsites\" ',"-1", (char *)0);
		die("execl");

	}
	else if(pid==1) {

	    close(link[1]);
	    int nbytes = read(link[0], foo, sizeof(foo));
	    printf("Output: (%.*s)\n", nbytes, foo);
	    wait(NULL);

  	}
  	return 0;
	

	

}