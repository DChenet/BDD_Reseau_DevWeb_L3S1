#include <netinet/in.h>
#include <sys/socket.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <time.h>
#include <unistd.h>

int main(int argc, char* argv[])
{
    system("gcc serveur.c -o app `pkg-config --cflags --libs libpq`");
    int nbPassages = 0;
    char port[5];
    if(argc==3)
    {
      strcpy(port, argv[2]);
    }
    else if(argc==2)
    {
      strcpy(port, "5000");
    }
    else
    {
      printf("Format : ./executable addr_IP port \n");
      exit(1);
    }
    while(1)
    {
        char commande[100] = "";
        strcat(commande, "./app ");
        strcat(commande, argv[1]);
        strcat(commande, " ");
        strcat(commande, port);
        //system("clear");
        if(nbPassages == 0)
        {
            printf("\nLANCEMENT DU SERVEUR AU BOUT DE 5s\n");
            usleep(5000000);
            system(commande);
        }
        else
        {
            printf("\nLANCEMENT DU SERVEUR AU BOUT DE 1m\n");
            usleep(60000000);
            system(commande);
        }
        nbPassages++;
    }
}
