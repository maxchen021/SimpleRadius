using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Security.Principal;
using System.Diagnostics;

namespace wifi_client_tool_for_windows
{
    public partial class Form1 : Form
    {

        private string ca_cert_filename = "wifi_client_tool_ca_cert.crt";
        private string wifi_profile_filename = "wifi_client_tool_wifi_profile.xml";
        private int process_wait_time = 300000; //300 seconds
        private string ca_cert_file_fullpath = "";
        private string wifi_profile_file_fullpath = "";
        private string wifi_ssid = "";
        private string wifi_cert_fingerprint = "";
        private string ca_cert = "";


        public Form1()
        {
            InitializeComponent();
            ca_cert_file_fullpath = Path.Combine(Path.GetTempPath(), ca_cert_filename);
            wifi_profile_file_fullpath = Path.Combine(Path.GetTempPath(), wifi_profile_filename);
        }

        private void btnStart_Click(object sender, EventArgs e)
        {
            labelMsg.Text = "Please wait...";
            Boolean result;
            CreateCACertFile();
            result = ImportCACert();

            if (result==false)
            {
                labelMsg.Text = "Can't import radius certificate. Please try again!";
                labelMsg.ForeColor = Color.Red;
                return;
            }


            CreateWiFiProfileFile();
            result = AddWiFiProfile();

            if (result == false)
            {
                labelMsg.Text = "Can't create WiFi setting for \"" + wifi_ssid + "\". Please try again!";
                labelMsg.ForeColor = Color.Red;
                return;
            }

            labelMsg.Text = "Wifi setting for \"" + wifi_ssid + "\" has been setup successfully.";
        }

        private void CreateCACertFile()
        {
                    
            // Write the string to a file.
            System.IO.StreamWriter file = new System.IO.StreamWriter(ca_cert_file_fullpath);
            file.WriteLine(ca_cert);

            file.Close();

        }

        private Boolean ImportCACert()
        {
            System.Diagnostics.ProcessStartInfo myProcessInfo = new System.Diagnostics.ProcessStartInfo(); //Initializes a new ProcessStartInfo of name myProcessInfo
            myProcessInfo.FileName = Environment.ExpandEnvironmentVariables("%SystemRoot%") + @"\System32\cmd.exe"; //Sets the FileName property of myProcessInfo to %SystemRoot%\System32\cmd.exe where %SystemRoot% is a system variable which is expanded using Environment.ExpandEnvironmentVariables
            myProcessInfo.Arguments = "/C certutil -addstore \"Root\" \"" + ca_cert_file_fullpath + "\" && DEL /F /Q \"" + ca_cert_file_fullpath + "\""; //Sets the arguments           
            myProcessInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Hidden; //Sets the WindowStyle of myProcessInfo which indicates the window state to use when the process is started to Hidden
            myProcessInfo.Verb = "runas"; //The process should start with elevated permissions
            //System.Diagnostics.Process.Start(myProcessInfo); //Starts the process based on myProcessInfo
            var process = Process.Start(myProcessInfo);
            process.WaitForExit(process_wait_time);
            return !File.Exists(ca_cert_file_fullpath); //return true if file not exist, which mean it succeeded

        }

        public bool IsRunAsAdministrator()
        {
            bool isAdmin;
            try
            {
                WindowsIdentity user = WindowsIdentity.GetCurrent();
                WindowsPrincipal principal = new WindowsPrincipal(user);
                isAdmin = principal.IsInRole(WindowsBuiltInRole.Administrator);
            }
            catch
            {
                isAdmin = false;
            }
            
            return isAdmin;
        }

        private void RelaunchAsAdmin()
        {
            // Launch itself as administrator
            ProcessStartInfo proc = new ProcessStartInfo();
            proc.UseShellExecute = true;
            proc.WorkingDirectory = Environment.CurrentDirectory;
            proc.FileName = Application.ExecutablePath;
            proc.Verb = "runas";

            try
            {
                Process.Start(proc);
            }
            catch
            {
                // The user refused to allow privileges elevation.
                // Do nothing and return directly ...
                labelMsg.Text = "Please allow this program to run as administrator!";
                labelMsg.ForeColor = Color.Red;
                btnStart.Enabled = false;
                return;
            }

            Application.Exit();  // Quit itself
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            if (IsRunAsAdministrator() == true)
            {
                labelMsg.Text = "Please click on the \"Start\" button to setup your WiFi for \"" + wifi_ssid + "\""; 
            }
            else
            {
                RelaunchAsAdmin();
            }
          
        }

        private void CreateWiFiProfileFile()
        {
           string wifi_profile_config = "<?xml version=\'1.0\'?>" + Environment.NewLine
            + "<WLANProfile xmlns=\'http://www.microsoft.com/networking/WLAN/profile/v1\'>" + Environment.NewLine
            + "  <name>" + wifi_ssid + "</name>" + Environment.NewLine
            + "  <SSIDConfig>" + Environment.NewLine
            + "    <SSID>" + Environment.NewLine
            + "      <name>" + wifi_ssid + "</name>" + Environment.NewLine
            + "    </SSID>" + Environment.NewLine
            + "    <nonBroadcast>false</nonBroadcast>" + Environment.NewLine
            + "  </SSIDConfig>" + Environment.NewLine
            + "  <connectionType>ESS</connectionType>" + Environment.NewLine
            + "  <connectionMode>auto</connectionMode>" + Environment.NewLine
            + "  <autoSwitch>false</autoSwitch>" + Environment.NewLine
            + "  <MSM>" + Environment.NewLine
            + "    <security>" + Environment.NewLine
            + "      <authEncryption>" + Environment.NewLine
            + "        <authentication>WPA2</authentication>" + Environment.NewLine
            + "        <encryption>AES</encryption>" + Environment.NewLine
            + "        <useOneX>true</useOneX>" + Environment.NewLine
            + "        <FIPSMode xmlns=\'http://www.microsoft.com/networking/WLAN/profile/v2\'>false</FIPSMode>" + Environment.NewLine
            + "      </authEncryption>" + Environment.NewLine
            + "      <PMKCacheMode>enabled</PMKCacheMode>" + Environment.NewLine
            + "      <PMKCacheTTL>720</PMKCacheTTL>" + Environment.NewLine
            + "      <PMKCacheSize>128</PMKCacheSize>" + Environment.NewLine
            + "      <preAuthMode>disabled</preAuthMode>" + Environment.NewLine
            + "      <OneX xmlns=\'http://www.microsoft.com/networking/OneX/v1\'>" + Environment.NewLine
            + "        <cacheUserData>true</cacheUserData>" + Environment.NewLine
            + "        <authMode>user</authMode>" + Environment.NewLine
            + "        <EAPConfig><EapHostConfig xmlns=\'http://www.microsoft.com/provisioning/EapHostConfig\'><EapMethod><Type xmlns=\'http://www.microsoft.com/provisioning/EapCommon\'>25</Type><VendorId xmlns=\'http://www.microsoft.com/provisioning/EapCommon\'>0</VendorId><VendorType xmlns=\'http://www.microsoft.com/provisioning/EapCommon\'>0</VendorType><AuthorId xmlns=\'http://www.microsoft.com/provisioning/EapCommon\'>0</AuthorId></EapMethod><Config xmlns=\'http://www.microsoft.com/provisioning/EapHostConfig\'><Eap xmlns=\'http://www.microsoft.com/provisioning/BaseEapConnectionPropertiesV1\'><Type>25</Type><EapType xmlns=\'http://www.microsoft.com/provisioning/MsPeapConnectionPropertiesV1\'><ServerValidation><DisableUserPromptForServerValidation>false</DisableUserPromptForServerValidation><ServerNames></ServerNames><TrustedRootCA>"
            + wifi_cert_fingerprint + "</TrustedRootCA></ServerValidation><FastReconnect>true</FastReconnect><InnerEapOptional>false</InnerEapOptional><Eap xmlns=\'http://www.microsoft.com/provisioning/BaseEapConnectionPropertiesV1\'><Type>26</Type><EapType xmlns=\'http://www.microsoft.com/provisioning/MsChapV2ConnectionPropertiesV1\'><UseWinLogonCredentials>false</UseWinLogonCredentials></EapType></Eap><EnableQuarantineChecks>false</EnableQuarantineChecks><RequireCryptoBinding>false</RequireCryptoBinding><PeapExtensions><PerformServerValidation xmlns=\'http://www.microsoft.com/provisioning/MsPeapConnectionPropertiesV2\'>true</PerformServerValidation><AcceptServerName xmlns=\'http://www.microsoft.com/provisioning/MsPeapConnectionPropertiesV2\'>false</AcceptServerName></PeapExtensions></EapType></Eap></Config></EapHostConfig></EAPConfig>" + Environment.NewLine
            + "      </OneX>" + Environment.NewLine
            + "    </security>" + Environment.NewLine
            + "  </MSM>" + Environment.NewLine
            + "</WLANProfile>" + Environment.NewLine;

            // Write the string to a file.
            System.IO.StreamWriter file = new System.IO.StreamWriter(wifi_profile_file_fullpath);
            file.WriteLine(wifi_profile_config);

            file.Close();
        }

        private Boolean AddWiFiProfile()
        {
            System.Diagnostics.ProcessStartInfo myProcessInfo = new System.Diagnostics.ProcessStartInfo(); //Initializes a new ProcessStartInfo of name myProcessInfo
            myProcessInfo.FileName = Environment.ExpandEnvironmentVariables("%SystemRoot%") + @"\System32\cmd.exe"; //Sets the FileName property of myProcessInfo to %SystemRoot%\System32\cmd.exe where %SystemRoot% is a system variable which is expanded using Environment.ExpandEnvironmentVariables
            myProcessInfo.Arguments = "/C netsh wlan add profile filename=\"" + wifi_profile_file_fullpath + "\" && DEL /F /Q \"" + wifi_profile_file_fullpath + "\""; //Sets the arguments
            myProcessInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Hidden; //Sets the WindowStyle of myProcessInfo which indicates the window state to use when the process is started to Hidden
            myProcessInfo.Verb = "runas"; //The process should start with elevated permissions
            // System.Diagnostics.Process.Start(myProcessInfo); //Starts the process based on myProcessInfo
            var process = Process.Start(myProcessInfo);
            process.WaitForExit(process_wait_time);
            return !File.Exists(wifi_profile_file_fullpath); //return true if file not exist, which mean it succeeded

        }

    }
}
