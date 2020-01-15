import javax.swing.JPanel;
import javax.swing.JLabel;
import javax.swing.JFrame;
import java.awt.Color;
import java.awt.BorderLayout;
import java.awt.Font;
import java.awt.GridLayout;
import java.awt.Dimension;
import javax.swing.JTextField;
import javax.swing.JComboBox;
import javax.swing.JButton;
import java.io.IOException ;
import java.io.BufferedReader ;
import java.io.InputStreamReader ;
import java.io.PrintWriter ;
import java.io.IOException ;
import java.net.Socket ;
import java.net.UnknownHostException ;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.concurrent.*;
import java.util.Date;
import java.util.Timer;
import java.util.TimerTask;

public class Client extends JFrame {

	private static final long serialVersionUID = 123456;
	private JPanel container = new JPanel();
    public JTextField IP = new JTextField();
    public JTextField port = new JTextField("5000");
	private JTextField type = new JTextField("");
	private JTextField noTrajet = new JTextField("");
	private JTextField heure = new JTextField("");
	private JLabel titreNoTrajet = new JLabel("N° trajet :");
	private JLabel titreHeure = new JLabel("Heure :");
	private JLabel titreType = new JLabel("Type :");
	private JLabel message = new JLabel("");
	private JComboBox<String> combo;
	private JButton button = new JButton("Valider");
	private JButton button_quitter = new JButton("QUIT");
	private JButton button_connect = new JButton("CONNECT");
	public static Socket socket = null ;
    public static PrintWriter flux_sortie = null ;
    public static BufferedReader flux_entree = null;
    public String mode = "Nouvel Incident";
	public String chaine = "";

	JPanel deux = new JPanel(); // type de l incident

	public static void main(String[] args) throws IOException
	{
		Client client = new Client();
	}

	public Client()
	{
        IP.setText("127.0.0.1");
		// Caracteristiques generales fenetre
		this.setTitle("Client TCP");
		this.setSize(400, 400);
		this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		this.setLocationRelativeTo(null);

		// JPanel principal
		container.setBackground(Color.white);
		container.setLayout(new GridLayout(8, 1));

		// JPanel des elements de l app
		JPanel un = new JPanel(); // no de trajet
		JPanel trois = new JPanel(); // heure
		JPanel bas = new JPanel(); // bouton quitter & zone de message
        JPanel conn = new JPanel();

		Font police = new Font("Arial", Font.BOLD, 17);
		type.setFont(police);
		noTrajet.setFont(police);
		heure.setFont(police);
		titreNoTrajet.setFont(police);
		titreHeure.setFont(police);
		titreType.setFont(police);

        IP.setPreferredSize(new Dimension(250, 30));
		type.setPreferredSize(new Dimension(250, 30));
		noTrajet.setPreferredSize(new Dimension(250, 30));
		heure.setPreferredSize(new Dimension(250, 30));

		un.add(titreNoTrajet);
		un.add(noTrajet);
		deux.add(titreType);
		deux.add(type);
		trois.add(titreHeure);
		trois.add(heure);
		bas.add(message);
		bas.add(button_quitter);
        conn.add(IP);
        conn.add(port);

		button.addActionListener(new ItemAction());
		button_quitter.addActionListener(new ItemAction());
		button_connect.addActionListener(new ItemAction());

		String[] tab = {"Nouvel Incident", "Mise a jour"};
		combo = new JComboBox<String>(tab);
		combo.addActionListener(new ItemAction());

        container.add(conn);
		container.add(combo);
		container.add(un);
		container.add(deux);
		container.add(trois);
		container.add(button);
		container.add(bas);
		container.add(button_connect);

		this.setContentPane(container);
		this.setVisible(true);

		button_quitter.setVisible(false); // On ne peut pas quitter si l on n est pas connecte
		button.setVisible(false);
		container.revalidate();
		container.repaint();
	}

	/*
		Donne des instruction lord d un click d un utilisateur
	*/
	class ItemAction implements ActionListener
	{
		public void actionPerformed(ActionEvent e)
		{
			Object  source=e.getSource();

		    if  (source==button) // Si un click sur le bouton valide
		    {

				if(mode.equals("Nouvel Incident")) // Pour un nouvel incident
				{
					if(!type.getText().equals("") && !heure.getText().equals("") && !noTrajet.getText().equals("")) // Si on a bien tout rempli
					{
						try{
							message.setText("Nouvelle communication");
							echange(); // On commence l echange des messages
						}
						catch (IOException erreur) {
							System.out.println("Probleme : "+erreur);
							deco();
						}
					}
					else
						message.setText("Remplissez tous les champs");
				}
				else // Pour la mise a jour d un incident
				{
					if(!heure.getText().equals("") && !noTrajet.getText().equals(""))
					{
						try{
							message.setText("Nouvelle communication");
							echange();
						}
						catch (IOException erreur) {
							System.out.println("Probleme : "+erreur);
							deco();
						}
					}
					else
						message.setText("Remplissez tous les champs");
				}
			}

		    else if (source==combo) // Si le mode est change
		    {
				mode = combo.getSelectedItem().toString();

				if(mode.equals("Mise a jour"))
				{
					deux.setVisible(false); // Pas besoin du type pour la mise a jour
				}
				else
				{
					deux.setVisible(true);
				}
		    }
			else if (source==button_quitter)
			{
				deco();
			}
			else if (source == button_connect) // Pour se connecter au serveur
			{
				try
				{
					socket = new Socket (IP.getText(), Integer.parseInt(port.getText()));
					flux_sortie = new PrintWriter (socket.getOutputStream (), true);
					flux_entree = new BufferedReader (new InputStreamReader (socket.getInputStream ())) ;
					flux_sortie.println("Bien connecte");
					message.setText("Bien Connecte");

					// Si on est bien connecte on peut quitter
					button_quitter.setVisible(true);
					button_connect.setVisible(false);
					button.setVisible(true);
					container.revalidate();
					container.repaint();

				} catch(IOException er){}
			}
		}
	}

	/*
		Permet de se deconnecter du serveur
	*/
	public void deco()
	{
		try
		{
			message.setText("Deconnecte");
			flux_sortie.close();
			flux_entree.close();
			socket.close();
		}
		catch (IOException erreur) {
		}
		button_connect.setVisible(true);
		button_quitter.setVisible(false);
		button.setVisible(false);
		container.revalidate();
		container.repaint();
	}

	/*
		Echange de message si l on es bien connecte
		On reste dans cette methode tant que la communication est en cours
		Ou si il survient une erreur
	*/
    public void echange() throws IOException
    {
		System.out.println("Echange");
		chaine = "";
    	Boolean b = true;
		Boolean nIncident = true, nTrajet = true, echangeCommence = false;

    	while(b)
    	{
			if(!nIncident) // Si on n a encore envoye le premier message
			{
				recup rec = new recup();
				long ref = 0, time;
				ref = System.currentTimeMillis();
				Boolean verif = true;

				while(verif && rec.flag == false) // Permet de verifier que l on recoit un message dans les 20 secondes
				{
					time = System.currentTimeMillis() - ref;

					if(time/1000 >= 20)
					{
						verif = false; // Si timeout
					}
				}

				if(rec.flag == false) // Si il y a eu un probleme lors de la recuperation
				{
					b = false;
					deco();
					break;
				}

				System.out.println("chaine :"+chaine);

			}

			// On transmet le mode demande
			if (mode.equals("Nouvel Incident") && nIncident)
			{
				flux_sortie.println ("!INCIDENT");
				message.setText("Nouvelle communication");
				nIncident = false;
				echangeCommence = true;
			}
			else if(mode.equals("Mise a jour") && nIncident)
			{
				flux_sortie.println ("%ETAT");
				message.setText("Nouvelle communication");
				nIncident = false;
				echangeCommence = true;
			}

			else if(chaine == null) // Si aucun message n a ete recu
			{
				b = false;
				message.setText("Probleme de connexion");

				try{
					flux_sortie.println ("DECO");
					message.setText("Deconnecte");
					flux_sortie.close();
					flux_entree.close();
					socket.close();
					button.setVisible(false);
					button_connect.setVisible(true);
					button_quitter.setVisible(false);
					container.revalidate();
					container.repaint();
				}
				catch (IOException erreur) {
				}
			}

			else if (chaine.equals("? TRAJET") && nTrajet)
			{
				flux_sortie.println("NUM:"+noTrajet.getText());
				nTrajet = false;
				echangeCommence = true;
			}
			else if (chaine.equals("FIN"))
			{
				message.setText("Mauvais numero de trajet");
				b = false;
			}
			else if (chaine.equals("? TYPE"))
			{
				flux_sortie.println("TYPE:"+type.getText());
			}
			else if(chaine.equals("? HDEBUT"))
			{
				flux_sortie.println("H:"+heure.getText());
			}
			else if(chaine.equals("? RESOLU"))
			{
				flux_sortie.println("OUI");
			}
			else if(chaine.equals("OK")) // Si le protocole applicatif est complet
			{
				message.setText("Communication terminee");
				b = false;
			}
			else if(chaine.equals("DEBUT ECHANGE")){}
			else // Si le message ne correspond a rien que l on attend
			{
				if(echangeCommence == true)
				{
					message.setText("ERREUR");
					b = false;
				}
			}
    	}

	}

	/*
		Utilise un thread pour recuperer le message
		en parallele du lancement du chronometre pour le timeout
	*/
	class recup implements Runnable{

		Thread UnThread;
		public boolean flag = false;

		recup()
		{
			UnThread = new Thread(this, "thread secondaire");
			UnThread.start();
		}

		public void run()
		{

			try{
				System.out.println("attente du message");
				chaine = flux_entree.readLine();
			}catch(IOException e)
			{
				message.setText("Reception timeout");

				System.out.println("erreur : "+e);
				deco();
				flag = false;
				return;

			}
			flag = true;
		}
	}
}
