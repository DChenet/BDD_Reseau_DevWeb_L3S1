#include <netinet/in.h>
#include <sys/socket.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <time.h>
#include <unistd.h>
#include <signal.h>
// A CHANGER SELON LE SYSTEME
// Pour linux
//#include "/usr/include/postgresql/libpq-fe.h"
// Pour mac
#include "/usr/local/Cellar/libpq/11.5_1/include/libpq-fe.h"

// ligne de compilation :
// gcc serveur.c -o serveur `pkg-config --cflags --libs libpq`

char* requete(PGconn *conn, char *requete, int, int);
void connexion_TCP(char *adresse);
PGconn* connexion_PG(char *connexion);
void read_TCP(int sdial);
void write_TCP(int sdial, char *message);
void close_TCP(int, int);
void copy_part(char buf[80], char buf2[80], int debut, int fin_inclu);
void copy_part_end(char buf[80], char buf2[80], int debut);
char* getNumIncident(PGconn *conn);
void requeteSansRes(PGconn *conn, char *requete);
void remise_a_zero();

char buf[80];
char buf2[80];
char  port[5];
int s_ecoute = -1;
int sdial = -1;
int nincident = 0, nnum = 0, ntype = 0, nhdebut = 0, nfin = 0, nfinMaj = 0, ndebut = 0, indic = 0;

int main (int argc, char* argv[]) {

    if(argc==3)
    {
    strcpy(port, argv[2]);
    }
    else if(argc==2)
    {
    strcpy(port, "5000");
    }

    //PGconn *conn = connexion_PG("user=y2019l3i_dchenet password=A123456* host=10.40.128.23 dbname=db2019l3i_dchenet");
    PGconn *conn = connexion_PG("user=postgres password=postgres host=127.0.0.1 dbname=postgres");
    if(conn == NULL)
    {
    	printf("Connexion avec la base de donnees a echoue\n");
    	exit(1);
    }

    int maj = 0;
    int nco = 0;
    int numTrajet;
    int numIncident;
    char typeIncident[80];
    char heureDebut[80];
    int continuer = 1;

    while(continuer)
    {
        // Si aucune connexion n'a ete faite
        if(nco == 0)
        {
            connexion_TCP(argv[1]);
            if(sdial < 0)
            {
                printf("Lancement du serveur a echoue\n");
                exit(1);
            }
            nco = 1;
        }

        // Si on n'est pas dans la phase d'insertion dans la BDD
    	if((nfin == 0 && maj == 0) || (nfinMaj == 0 && maj == 1))
        {
        	read_TCP(sdial);
            if(strlen(buf) == 80)
            {
                printf("Depassement de buffer\n");
                close(s_ecoute);
                close(sdial);
                exit(1);
            }
            // Si il y a eu un probleme de recuperation du message
            // ou timeout
            if(strcmp(buf, "erreur")==0)
            {
                close(s_ecoute);
                close(sdial);
                sdial = -1;
                s_ecoute = -1;
                exit(1);
            }
		}

        printf("message recu : %s\n", buf);

        // Si on veut se deconnecter
        if(strcmp(buf, "DECO\n")==0)
        {
            nco = 0;
            shutdown(sdial, SHUT_RDWR);
            close(s_ecoute);
            close(sdial);
            sdial = -1;
            s_ecoute = -1;
            continuer = 0;
        }

        // Quand on veut report un nouvel incident
        else if(strcmp(buf, "!INCIDENT\n")==0)
        {
            write_TCP(sdial, "? TRAJET\n");
            nincident = 1;
            ndebut = 1;
            maj = 0;
        }

        // Quand on veut actualiser un incident
        else if(strcmp(buf, "\%ETAT\n")==0)
        {
            write_TCP(sdial, "? TRAJET\n");
            nincident = 1;
            ndebut = 1;
            maj = 1;
        }

        // Si on a bien recu un nouvel incident
        // de trajet
        else if(nincident == 1)
        {
            char * result = strstr( buf, "NUM:");
            // Si la commande est valide
            if(result != NULL)
            {
                copy_part_end(buf, buf2, 4);
                char query[500] = "SELECT no_trajet FROM TRAJET WHERE no_trajet = ";
                strcat(query, buf2);
                strcat(query, ";");
                char *res = requete(conn, query, 0, 0);

                // Si le numero de trajet n'existe pas
                if(strcmp(res, "") == 0)
                {
                    write_TCP(sdial, "FIN\n");
                    remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
                }
                else if (maj ==0)
                {
                    numTrajet = atoi(buf2);
                    write_TCP(sdial, "? TYPE\n");
                    nincident = 0;
                    nnum = 1;
                }
                else
                {
                    numTrajet = atoi(buf2);
                    write_TCP(sdial, "? HDEBUT\n");
                    nincident = 0;
                    nhdebut = 1;
                }
            }
            else
            {
                remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
            }
        }

        // Si on est bien passe par la demande de numero de trajet
        else if(nnum == 1 && maj == 0)
        {
            char *result = strstr(buf, "TYPE:");
            // Si la commande est valide
            if(result != NULL)
            {
                copy_part_end(buf, buf2, 5);
                strcpy(typeIncident, buf2);
                write_TCP(sdial, "? HDEBUT\n");
                nhdebut = 1;
                nnum = 0;
            }
            else
            {
                remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
            }
        }

        // Si on est bien passes par la demande d'heure
        else if(nhdebut == 1)
        {
        	// Si la commande est valide
            char *result = strstr(buf, "H:");
            if(result != NULL)
            {
                if(maj == 0)
                {
                    copy_part_end(buf, buf2, 2);
                    strcpy(heureDebut, buf2);
                    nhdebut = 0;
                    nfin = 1;
                }
                else
                {
                    copy_part_end(buf, buf2, 2);
                    strcpy(heureDebut, buf2);
                    write_TCP(sdial, "? RESOLU\n");
                    nhdebut = 0;
                    indic = 1;
                }

            }
            else
            	remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
        }

        // Si on a toutes les infos et qu'on veut les inserer dans la BDD
		else if(nfin == 1 || indic == 1)
		{
            if(maj == 0)
            {
                nfinMaj = 1;
                char date[50];
                time_t timestamp = time(NULL);
                strftime(date, sizeof(date), "%Y-%m-%d", localtime(&timestamp));
                char *cnumIncident = getNumIncident(conn);

                char requete[500] = "INSERT INTO incident VALUES ('";
                strcat(requete,  cnumIncident);
                strcat(requete, "', '");
                strcat(requete, date);
                strcat(requete, "', '");
                strcat(requete, heureDebut);
                strcat(requete, "', '");
                strcat(requete, typeIncident);
                strcat(requete, "', FALSE);");
                requeteSansRes(conn, requete);

                char requete2[500] = "INSERT INTO se_produit VALUES (";
                strcat(requete2, cnumIncident);
                strcat(requete2, ", ");
                char num[5];
                sprintf(num, "%d", numTrajet);
                strcat(requete2, num);
                strcat(requete2, ");");
                requeteSansRes(conn, requete2);
                write_TCP(sdial, "OK\n");
                remise_a_zero();
            }

            else if(maj == 1)
            {
                char *result = strstr(buf, "OUI");
                // Si la commande est valide

                if(result != NULL)
                {

                    char Requete[500] = "SELECT no_incident FROM se_produit NATURAL JOIN incident WHERE no_trajet= ";
                    char num[5];
                    sprintf(num, "%d", numTrajet);
                    strcat(Requete, num);
                    strcat(Requete, " AND heure_incident = '");
                    strcat(Requete, heureDebut);
                    strcat(Requete, "';");

                    char *resultat = requete(conn, Requete, -1, 0);

                    if(strcmp(resultat, "") == 0)
                    {
                        write_TCP(sdial, "Incident non existant\n");
                        remise_a_zero();
                    }
                    else
                    {
                        char requete2[500] = "UPDATE incident SET resolu = true WHERE no_incident =";
                        strcat(requete2, resultat);
                        strcat(requete2, ";");
                        requeteSansRes(conn, requete2);
                        write_TCP(sdial, "OK\n");
                        remise_a_zero();
                    }

                }
                else
                {
                    remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
                }
            }
		}

        else if (strcmp(buf, "") == 0)
        {
            exit(1);
        }

		else
        {
            remise_a_zero(sdial, &nincident, &nnum, &ntype, &nhdebut, &nfin);
        }

	}

}

void connexion_TCP( char *adresse)
{
    printf("Serveur a l adresse : %s\net au port : %s\n", adresse, port);
    int option = 1;
    unsigned int cli_len ;
	struct sockaddr_in serv_addr, cli_addr ;
    printf("Attente de connexion\n");
	serv_addr.sin_family = AF_INET ;
    serv_addr.sin_port = htons (atoi(port));
    inet_aton(adresse, &serv_addr.sin_addr);
	memset (&serv_addr.sin_zero, 0, sizeof(serv_addr.sin_zero));
	s_ecoute = socket (PF_INET, SOCK_STREAM, 0) ;
	bind (s_ecoute, (struct sockaddr *)&serv_addr, sizeof serv_addr) ;

	listen (s_ecoute, 10);
	cli_len = sizeof (cli_addr) ;
	sdial = accept (s_ecoute, (struct sockaddr *)&cli_addr, &cli_len) ;

	printf ("Le client d'adresse IP %s s'est connecté depuis son port %d\n", \
	            inet_ntoa (cli_addr.sin_addr), ntohs (cli_addr.sin_port)) ;
}

void remise_a_zero()
{
	write_TCP(sdial, "DEBUT ECHANGE\n");
	nincident = 0;
	nnum = 0;
	ntype = 0;
	nhdebut = 0;
	nfin = 0;
    nfinMaj = 0;
    ndebut = 0;
}

void copy_part_end(char buf[80], char buf2[80], int debut)
{
    memset(buf2, 0, 80);
    int i = debut;
    int j = 0;
    while(buf[i] != '\n')
    {
        buf2[j] = buf[i];
        i++;
        j++;
    }
}

void copy_part(char buf[80], char buf2[80], int debut, int fin_inclu)
{
    memset(buf2, 0, 80);
    for(int i=debut; i<=fin_inclu; i++)
    {
        buf2[i] = buf[i];
    }
}

void close_TCP(int s_dial, int s_ecoute)
{
    close (s_dial);
	close (s_ecoute);
}

void write_TCP(int s_dial, char *message)
{
    printf("message envoye : %s\n", message);
    char buffer[80];
    memset(buffer, 0, 80);
    strcpy(buffer, message);
    write (s_dial, buffer, strlen (buffer));
}

void read_TCP(int s_dial)
{
    // Initialize file descriptor sets
    fd_set read_fds, write_fds, except_fds;
    FD_ZERO(&read_fds);
    FD_ZERO(&write_fds);
    FD_ZERO(&except_fds);
    FD_SET(sdial, &read_fds);

    // Set timeout to 1.0 seconds
    struct timeval timeout;
    timeout.tv_sec = 10;
    timeout.tv_usec = 0;

    // Wait for input to become ready or until the time out; the first parameter is
    // 1 more than the largest file descriptor in any of the sets
    if (select(sdial + 1, &read_fds, &write_fds, &except_fds, &timeout) == 1 && ndebut == 1)
    {
        memset(buf, 0, 80);
        read(s_dial, buf, 80);
    }

    else if (!ndebut)
    {
        memset(buf, 0, 80);
        read(s_dial, buf, 80);
    }

    else
    {
        memset(buf, 0, 80);
        strcpy(buf, "erreur");
    }

}

/*
// Permet de recuperer un numero d incident
// non deja utilise afin de pouvoir l inserer
*/
char* getNumIncident(PGconn *conn)
{
    char *derNum = malloc(5*sizeof(char));
    strcpy(derNum, "1");
    char *res = requete(conn, "SELECT no_incident FROM incident;", -1, 0);
    if(strcmp(res, "")!=0)
    {
        int num = atoi(res)+1;
        sprintf(derNum, "%d", num);
    }
    return derNum;
}

/*
// Permet de faire une requete
// qui ne necessite pas de resultats
*/
void requeteSansRes(PGconn *conn, char *requete)
{
    PGresult* res = NULL;
    res = PQexec(conn, requete);
    if(PQresultStatus(res) != PGRES_COMMAND_OK)
    {
        printf("ERREUR LORS DE LA REQUETE : %s\n", requete);
        write_TCP(sdial, "FIN");
        PQfinish(conn);
        exit(1);
    }
}
/*
// Cette fonction permet de faire une requete avec un resultat
// Si nligne et ncolonne valent 0 on recupere toute la reponse
// Si nligne = -1 on ne considere que la premiere colonne
*/
char* requete(PGconn *conn, char *requete, int nligne, int ncolonne)
{
    char *resultat = malloc(500*sizeof(char));
    strcpy(resultat, "");

    PGresult* res = NULL;

    res = PQexec(conn, requete);

    if(PQresultStatus(res) != PGRES_TUPLES_OK)
    {
        printf("ERREUR LORS DE LA REQUETE : %s\n", requete);
        write_TCP(sdial, "FIN");
        PQfinish(conn);
        exit(1);
    }
    else
    {
        printf("REQUETE REUSSIE \n");
    }
    // On recupere le nombre de lignes et de colonnes de notre resultat de requete
    int nblignes = PQntuples(res);
    int nbcolonnes = PQnfields(res);

    if(nligne == 0 && ncolonne == 0)
    {
        for(int i=0; i<nblignes; i++)
        {
            for(int j=0; j<nbcolonnes; j++)
            {
                char buf[80];
                memset (buf, 0, 80);
                strcpy(buf, PQgetvalue(res, i, j));
                strcat(resultat, buf);
                if(j<nbcolonnes-1)
                    strcat(resultat, ",");
            }
            strcat(resultat, "\n");
        }
    }

    else if(nligne == -1)
    {
        char buf[80];
        memset (buf, 0, 80);
        if(nblignes == 0)
        	strcpy(buf, "");
        else
        {
            int max = atoi(PQgetvalue(res, 0, ncolonne));
            for(int i=1; i<nblignes; i++)
            {
                if(atoi(PQgetvalue(res, i, ncolonne))>max)
                {
                    max = atoi(PQgetvalue(res, i, ncolonne));
                }
            }
            sprintf(buf, "%d", max);

        }
        strcat(resultat, buf);
    }

    return resultat;
}

/*
// Permet de se connecter a la base de donnee
// de parametre connexion
*/
PGconn* connexion_PG(char *connexion)
{
    // "user=postgres password=postgres host=127.0.0.1 dbname=postgres"
    PGconn *conn = PQconnectdb(connexion);
    if(PQstatus(conn) != CONNECTION_OK)
        return NULL;
    else
        return conn;
}
