"""
Script de Entrenamiento del Modelo de Clasificacin de Riesgo de Visas

Entrena un modelo Random Forest para clasificar el riesgo de vencimiento
en tres categoras: alto_riesgo, medio_riesgo, bajo_riesgo.
"""

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import (
    classification_report, 
    confusion_matrix, 
    accuracy_score,
    precision_recall_fscore_support
)
import joblib
import json
from datetime import datetime


class VisaRiskModel:
    """Modelo de clasificacin de riesgo de vencimiento de visas."""
    
    def __init__(self):
        self.model = None
        self.label_encoder = LabelEncoder()
        self.feature_names = []
        self.pais_encoder = LabelEncoder()
        self.tipo_visa_encoder = LabelEncoder()
        
    def preparar_features(self, df: pd.DataFrame, fit: bool = False) -> np.ndarray:
        """
        Prepara las features para el modelo.
        
        Args:
            df: DataFrame con los datos
            fit: Si True, ajusta los encoders (solo para training)
            
        Returns:
            Array numpy con las features preparadas
        """
        df_copy = df.copy()
        
        # Encoding de variables categricas
        if fit:
            df_copy['pais_encoded'] = self.pais_encoder.fit_transform(df_copy['pais'])
            df_copy['tipo_visa_encoded'] = self.tipo_visa_encoder.fit_transform(df_copy['tipo_visa'])
        else:
            # Para prediccin, manejar valores no vistos
            df_copy['pais_encoded'] = df_copy['pais'].apply(
                lambda x: self.pais_encoder.transform([x])[0] 
                if x in self.pais_encoder.classes_ 
                else -1
            )
            df_copy['tipo_visa_encoded'] = df_copy['tipo_visa'].apply(
                lambda x: self.tipo_visa_encoder.transform([x])[0] 
                if x in self.tipo_visa_encoder.classes_ 
                else -1
            )
        
        # Seleccionar features numricas
        features = [
            'edad',
            'pais_encoded',
            'tipo_visa_encoded',
            'renovaciones_previas',
            'dias_restantes',
            'dias_desde_inicio',
            'porcentaje_transcurrido',
            'en_ultimos_3_meses'
        ]
        
        if fit:
            self.feature_names = features
        
        return df_copy[features].values
    
    def entrenar(self, X_train: np.ndarray, y_train: np.ndarray):
        """
        Entrena el modelo Random Forest.
        
        Args:
            X_train: Features de entrenamiento
            y_train: Labels de entrenamiento
        """
        print(" Entrenando Random Forest Classifier...")
        
        self.model = RandomForestClassifier(
            n_estimators=100,
            max_depth=10,
            min_samples_split=5,
            min_samples_leaf=2,
            random_state=42,
            n_jobs=-1,
            class_weight='balanced'  # Manejar desbalanceo si existe
        )
        
        self.model.fit(X_train, y_train)
        print(" Modelo entrenado exitosamente")
    
    def evaluar(self, X_test: np.ndarray, y_test: np.ndarray) -> dict:
        """
        Evala el modelo en el conjunto de prueba.
        
        Args:
            X_test: Features de prueba
            y_test: Labels de prueba
            
        Returns:
            Diccionario con mtricas de evaluacin
        """
        print("\n Evaluando modelo...")
        
        # Predicciones
        y_pred = self.model.predict(X_test)
        
        # Mtricas
        accuracy = accuracy_score(y_test, y_pred)
        precision, recall, f1, _ = precision_recall_fscore_support(
            y_test, y_pred, average='weighted'
        )
        
        # Reporte detallado
        print("\n" + "="*60)
        print("REPORTE DE CLASIFICACIN")
        print("="*60)
        print(classification_report(y_test, y_pred))
        
        # Matriz de confusin
        print("\n" + "="*60)
        print("MATRIZ DE CONFUSIN")
        print("="*60)
        cm = confusion_matrix(y_test, y_pred)
        print(cm)
        
        # Feature importance
        print("\n" + "="*60)
        print("IMPORTANCIA DE FEATURES")
        print("="*60)
        importances = self.model.feature_importances_
        for name, importance in zip(self.feature_names, importances):
            print(f"{name:30s}: {importance:.4f}")
        
        metrics = {
            'accuracy': float(accuracy),
            'precision': float(precision),
            'recall': float(recall),
            'f1_score': float(f1),
            'confusion_matrix': cm.tolist(),
            'feature_importance': {
                name: float(imp) 
                for name, imp in zip(self.feature_names, importances)
            }
        }
        
        return metrics
    
    def predecir(self, X: np.ndarray) -> tuple:
        """
        Realiza predicciones con el modelo.
        
        Args:
            X: Features para predecir
            
        Returns:
            Tupla (predicciones, probabilidades)
        """
        predicciones = self.model.predict(X)
        probabilidades = self.model.predict_proba(X)
        
        return predicciones, probabilidades
    
    def guardar(self, ruta_modelo: str = 'models/visa_risk_classifier.pkl'):
        """Guarda el modelo entrenado."""
        modelo_completo = {
            'model': self.model,
            'label_encoder': self.label_encoder,
            'pais_encoder': self.pais_encoder,
            'tipo_visa_encoder': self.tipo_visa_encoder,
            'feature_names': self.feature_names,
            'fecha_entrenamiento': datetime.now().isoformat()
        }
        
        joblib.dump(modelo_completo, ruta_modelo)
        print(f"\n Modelo guardado en: {ruta_modelo}")
    
    @classmethod
    def cargar(cls, ruta_modelo: str = 'models/visa_risk_classifier.pkl'):
        """Carga un modelo previamente entrenado."""
        modelo_completo = joblib.load(ruta_modelo)
        
        instance = cls()
        instance.model = modelo_completo['model']
        instance.label_encoder = modelo_completo['label_encoder']
        instance.pais_encoder = modelo_completo['pais_encoder']
        instance.tipo_visa_encoder = modelo_completo['tipo_visa_encoder']
        instance.feature_names = modelo_completo['feature_names']
        
        return instance


def main():
    """Funcin principal de entrenamiento."""
    print("="*60)
    print("ENTRENAMIENTO DEL MODELO DE CLASIFICACIN DE RIESGO")
    print("="*60)
    
    # 1. Cargar datos
    print("\n Cargando datos...")
    df_train = pd.read_csv('data/visa_data_train.csv')
    df_test = pd.read_csv('data/visa_data_test.csv')
    
    print(f" Datos de entrenamiento: {len(df_train)} registros")
    print(f" Datos de prueba: {len(df_test)} registros")
    
    # 2. Inicializar modelo
    modelo = VisaRiskModel()
    
    # 3. Preparar features
    print("\n Preparando features...")
    X_train = modelo.preparar_features(df_train, fit=True)
    y_train = df_train['riesgo'].values
    
    X_test = modelo.preparar_features(df_test, fit=False)
    y_test = df_test['riesgo'].values
    
    print(f" Features preparadas: {X_train.shape[1]} features")
    
    # 4. Entrenar modelo
    modelo.entrenar(X_train, y_train)
    
    # 5. Validacin cruzada
    print("\n Realizando validacin cruzada (5-fold)...")
    cv_scores = cross_val_score(
        modelo.model, X_train, y_train, 
        cv=5, scoring='accuracy', n_jobs=-1
    )
    print(f" Accuracy CV: {cv_scores.mean():.4f} (+/- {cv_scores.std() * 2:.4f})")
    
    # 6. Evaluar en test set
    metrics = modelo.evaluar(X_test, y_test)
    
    # 7. Guardar modelo
    modelo.guardar()
    
    # 8. Guardar mtricas
    metrics['cv_accuracy_mean'] = float(cv_scores.mean())
    metrics['cv_accuracy_std'] = float(cv_scores.std())
    
    with open('models/metrics.json', 'w') as f:
        json.dump(metrics, f, indent=2)
    
    print("\n Mtricas guardadas en: models/metrics.json")
    
    # 9. Resumen final
    print("\n" + "="*60)
    print("RESUMEN FINAL")
    print("="*60)
    print(f" Accuracy: {metrics['accuracy']:.4f}")
    print(f" Precision: {metrics['precision']:.4f}")
    print(f" Recall: {metrics['recall']:.4f}")
    print(f" F1-Score: {metrics['f1_score']:.4f}")
    
    if metrics['accuracy'] >= 0.85:
        print("\n Modelo cumple con el objetivo de accuracy > 85%!")
    else:
        print(f"\n Modelo por debajo del objetivo (accuracy: {metrics['accuracy']:.2%})")
    
    print("\n Entrenamiento completado exitosamente!")


if __name__ == '__main__':
    main()

